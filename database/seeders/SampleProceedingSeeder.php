<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\BoardMember;
use App\Models\Issue;
use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleProceedingSeeder extends Seeder
{
    /**
     * Clearly labelled sample data for local development.
     */
    public function run(): void
    {
        $issue = Issue::updateOrCreate(['slug' => 'sample-proceedings'], [
            'title' => 'Sample Proceedings of ICSMI',
            'year' => (int) now()->format('Y'),
            'volume' => 1,
            'description' => 'Sample data for development. Replace before launch.',
            'published_at' => now()->subDay(),
        ]);

        $samples = [
            ['Sample Article on Sustainable Supply Chains', ['Sample Author One', 'Sample Author Two'], ['sustainability', 'supply chain'], 1, 8],
            ['Sample Article on Green Innovation in Small Firms', ['Sample Author Three'], ['innovation', 'small firms'], 9, 16],
            ['Sample Article on Responsible Management Education', ['Sample Author Four', 'Sample Author Five'], ['management', 'education'], 17, 24],
        ];

        foreach ($samples as $index => [$title, $authors, $keywords, $first, $last]) {
            $slug = 'sample-article-'.($index + 1);
            $path = 'articles/'.$slug.'.pdf';
            Storage::disk('local')->put($path, $this->minimalPdf());

            $article = Article::updateOrCreate(['slug' => $slug], [
                'issue_id' => $issue->id,
                'title' => $title,
                'abstract' => 'This is sample abstract text for development only. It does not describe real research.',
                'first_page' => $first,
                'last_page' => $last,
                'pdf_path' => $path,
                'published_at' => now()->subDay(),
            ]);

            $article->authors()->delete();
            foreach ($authors as $position => $name) {
                $article->authors()->create(['name' => $name, 'affiliation' => 'Sample University', 'position' => $position]);
            }

            $article->keywords()->sync(collect($keywords)->map(fn ($name) => Keyword::firstOrCreate(['name' => $name])->id));
        }

        Announcement::updateOrCreate(['title' => 'Sample announcement'], [
            'body' => 'Sample data for development. Replace before launch.',
            'published_at' => now()->subDay(),
        ]);

        BoardMember::updateOrCreate(['group' => BoardMember::GROUP_EDITORIAL, 'name' => 'Sample Editor'], [
            'position' => 'Editor in Chief',
            'affiliation' => 'Sample University',
        ]);
    }

    private function minimalPdf(): string
    {
        return "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
    }
}
