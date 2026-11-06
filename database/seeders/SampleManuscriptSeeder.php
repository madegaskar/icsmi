<?php

namespace Database\Seeders;

use App\Enums\ManuscriptStatus;
use App\Models\Manuscript;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleManuscriptSeeder extends Seeder
{
    /**
     * One clearly labelled manuscript waiting for the sample editor.
     */
    public function run(): void
    {
        $author = User::where('email', 'author@icsmi.test')->firstOrFail();

        if (Manuscript::where('submitter_id', $author->id)->exists()) {
            return;
        }

        $manuscript = Manuscript::create([
            'submitter_id' => $author->id,
            'title' => 'Sample Manuscript on Circular Business Models',
            'abstract' => 'This is sample abstract text for development only. It does not describe real research.',
            'keywords' => 'sample, circular economy',
            'status' => ManuscriptStatus::Submitted,
            'submitted_at' => now(),
        ]);
        $manuscript->update(['code' => sprintf('ICSMI-%d-%04d', now()->year, $manuscript->id)]);

        $manuscript->authors()->create([
            'name' => $author->name,
            'email' => $author->email,
            'affiliation' => 'Sample University',
            'is_corresponding' => true,
        ]);

        $path = 'manuscripts/'.$manuscript->id.'/sample-blinded.pdf';
        $contents = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
        Storage::disk('local')->put($path, $contents);

        $manuscript->files()->create([
            'uploaded_by' => $author->id,
            'version' => 1,
            'kind' => 'blinded',
            'path' => $path,
            'original_name' => 'sample-blinded.pdf',
            'extension' => 'pdf',
            'size' => strlen($contents),
            'sha256' => hash('sha256', $contents),
        ]);

        $manuscript->histories()->create([
            'actor_id' => $author->id,
            'to_status' => ManuscriptStatus::Submitted,
            'created_at' => now(),
        ]);
    }
}
