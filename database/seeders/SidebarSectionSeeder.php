<?php

namespace Database\Seeders;

use App\Models\SidebarSection;
use Illuminate\Database\Seeder;

class SidebarSectionSeeder extends Seeder
{
    /**
     * The sidebar as it was before it became editable. Does nothing when any section already exists.
     */
    public function run(): void
    {
        if (SidebarSection::query()->exists()) {
            return;
        }

        $sections = [
            ['QUICK MENU', SidebarSection::KIND_LINKS, "Focus and Scope | /scope\nEditorial Board | /editorial-board\nCall for Papers | /call-for-papers\nImportant Dates | /important-dates\nAuthor Guidelines | /author-guidelines\nPublication Ethics | /ethics\nRegistration and Fees | /registration"],
            ['INDEXING', SidebarSection::KIND_TEXT, 'Target indexing: Scopus, Web of Science, Google Scholar, and Dimensions.'],
            ['TEMPLATE', SidebarSection::KIND_TEXT, 'The paper template is listed on the [Author Guidelines](/author-guidelines) page.'],
            ['ACCREDITATION', SidebarSection::KIND_TEXT, 'Accreditation information will be shown here.'],
            ['VISITOR', SidebarSection::KIND_VISITOR, null],
            ['INFORMATION', SidebarSection::KIND_LINKS, "For Readers | /for-readers\nFor Authors | /submission\nFor Librarians and Institutions | /contact"],
        ];

        foreach ($sections as $order => [$title, $kind, $body]) {
            SidebarSection::create([
                'title' => $title,
                'kind' => $kind,
                'body' => $body,
                'sort_order' => ($order + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
