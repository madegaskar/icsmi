# Graph Report - icsmi-baru  (2026-11-07)

## Corpus Check
- 303 files · ~50,799 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 16 file(s) not represented in the graph (top: (none) 11, .example 1, .xml 1)

## Summary
- 1932 nodes · 3861 edges · 372 communities (53 shown, 319 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 100 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4227fc14`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- MenuAndHomeTest.php
- ReportBuilder
- ContentImporter
- Illuminate\Http\RedirectResponse
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Model
- Manuscript
- ProfileTabsAndBellTest
- Illuminate\View\View
- PublicSubmissionTest.php
- PublicSubmissionTest
- SecurityHardeningTest.php
- MenuAndHomeTest
- package.json
- AdminAndReportsTest
- Media
- ManuscriptStatus
- PagesAndRevisionsTest
- ReviewStatus
- Risiko dan penanganannya
- Illuminate\Http\Request
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Database\Eloquent\Builder
- Illuminate\Database\Schema\Blueprint
- AnnouncementsAndContentTest
- web.php
- Page
- ArticleStat
- ICSMI
- MediaLibraryTest
- SecurityFoundationTest
- ManuscriptStatus
- .record
- ArticlePublisher.php
- MenuItem
- .record
- Article
- App\Http\Controllers\Admin\UserController
- ArticleFactory.php
- ManuscriptDocument
- ManuscriptSubmitted
- Announcement
- Closure
- Illuminate\Contracts\Validation\Validator
- PublicSiteTest
- Manuscript.php
- Illuminate\Support\Facades\Schema
- Illuminate\Support\Facades\Schema
- ManuscriptWorkflowTest.php
- SidebarSection.php
- BoardMember
- ReviewerController.php
- ReviewAssignment
- composer.json
- require-dev
- scripts
- VisitorStatsTest
- App\Http\Controllers\Admin\ContentController
- config
- 2026_11_04_000001_connect_public_submissions_to_manuscripts.php
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- psr-4
- require
- 0001_01_01_000001_create_cache_table.php
- 2026_11_03_000001_add_academic_fields_to_users_table.php
- 2026_11_03_000006_add_manuscript_id_to_articles_table.php
- 2026_11_08_000001_add_publishing_to_pages_table.php
- 2026_11_09_000001_add_category_and_end_date_to_announcements_table.php
- 2026_11_10_000001_add_reference_list_to_articles_table.php
- 2026_11_13_000001_add_cover_media_to_home_blocks_table.php
- 2026_11_14_000001_add_contact_and_notification_settings_to_users_table.php
- 2026_11_15_000001_add_consent_to_users_table.php
- User
- Laravel Application
- Laravel Application
- 2026_11_07_000001_create_menu_items_table.php
- 2026_11_07_000002_create_home_blocks_table.php
- 2026_11_08_000002_create_page_revisions_table.php
- 2026_11_12_000001_create_sidebar_sections_table.php
- LoginRequest
- ExampleTest
- author/show.blade.php
- public-layout.blade.php
- autoload-dev
- extra
- pages/tabs.blade.php
- editor/index.blade.php
- editor/show.blade.php
- auth.partials.academic-fields
- layouts.navigation
- profile.partials.update-password-form
- profile.tabs.
- announcements/form.blade.php
- announcements/index.blade.php
- audit-log.blade.php
- board/form.blade.php
- board/index.blade.php
- content/index.blade.php
- preview.blade.php
- admin/dashboard.blade.php
- home-blocks/form.blade.php
- home-blocks/index.blade.php
- menu/form.blade.php
- menu/index.blade.php
- pages/form.blade.php
- pages/index.blade.php
- revisions.blade.php
- sidebar/form.blade.php
- sidebar/index.blade.php
- users/edit.blade.php
- users/index.blade.php
- author/create.blade.php
- author/index.blade.php
- articles/form.blade.php
- articles/index.blade.php
- issues/form.blade.php
- issues/index.blade.php
- media/form.blade.php
- media/index.blade.php
- submissions/index.blade.php
- submissions/show.blade.php
- reviewer/show.blade.php
- proceedings.blade.php
- home.blade.php
- page.blade.php
- header.blade.php
- proceedings/show.blade.php
- search.blade.php
- 287487a7a985d76f0279cc8516b7a90b.php
- StatusBadgeTest

## God Nodes (most connected - your core abstractions)
1. `User` - 107 edges
2. `Controller` - 66 edges
3. `Manuscript` - 65 edges
4. `Role` - 65 edges
5. `ManuscriptStatus` - 55 edges
6. `TestCase` - 54 edges
7. `ManuscriptWorkflowTest` - 45 edges
8. `Article` - 42 edges
9. `Risiko dan penanganannya` - 40 edges
10. `AdminAndReportsTest` - 39 edges

## Surprising Connections (you probably didn't know these)
- `PublicSubmissionTest` --references--> `User`  [EXTRACTED]
  tests/Feature/PublicSubmissionTest.php → app/Models/User.php
- `MenuAndHomeTest` --references--> `User`  [EXTRACTED]
  tests/Feature/MenuAndHomeTest.php → app/Models/User.php
- `AdminAndReportsTest` --references--> `User`  [EXTRACTED]
  tests/Feature/AdminAndReportsTest.php → app/Models/User.php
- `ManuscriptWorkflowTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ManuscriptWorkflowTest.php → app/Models/User.php
- `PagesAndRevisionsTest` --references--> `User`  [EXTRACTED]
  tests/Feature/PagesAndRevisionsTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (372 total, 319 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.06
Nodes (10): AuditFixesTest, AuthenticationTest, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, InformationTableTest, ProfileTest (+2 more)

### Community 1 - "MenuAndHomeTest.php"
Cohesion: 0.10
Nodes (8): DatabaseSeeder, HomeBlockSeeder, MenuSeeder, PageSeeder, SampleAccountSeeder, SampleManuscriptSeeder, SampleProceedingSeeder, SidebarSectionSeeder

### Community 2 - "ReportBuilder"
Cohesion: 0.13
Nodes (4): {closure#1}(), {closure#2}(), ReportController, ReportBuilder

### Community 3 - "ContentImporter"
Cohesion: 0.08
Nodes (10): PageSlug, {closure#1}(), {closure#2}(), {closure#3}(), ContentImporter, {closure#1}(), {closure#2}(), {closure#3}() (+2 more)

### Community 4 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.07
Nodes (12): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, {closure#1}(), NewPasswordController, PasswordController, PasswordResetLinkController (+4 more)

### Community 6 - "AppServiceProvider.php"
Cohesion: 0.07
Nodes (9): AppServiceProvider, {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), PublicUrlTest (+1 more)

### Community 7 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.11
Nodes (4): {closure#1}(), ArticleAuthor, Keyword, StatusHistory

### Community 8 - "Manuscript"
Cohesion: 0.05
Nodes (27): {closure#3}(), AuthorManuscriptController, StoreManuscriptRequest, Manuscript, ManuscriptPolicy, {closure#1}(), {closure#4}(), {closure#5}() (+19 more)

### Community 9 - "ProfileTabsAndBellTest"
Cohesion: 0.09
Nodes (3): HeroImageUploadTest, ProfileTabsAndBellTest, SidebarEditorTest

### Community 10 - "Illuminate\View\View"
Cohesion: 0.14
Nodes (4): PageController, ProceedingController, AppLayout, GuestLayout

### Community 11 - "PublicSubmissionTest.php"
Cohesion: 0.12
Nodes (3): EmailVerificationTest, foreach(), test_confirmation_mail_to_one_address_is_limited_per_day()

### Community 12 - "PublicSubmissionTest"
Cohesion: 0.09
Nodes (6): {closure#2}(), {closure#3}(), SubmissionController, PublicSubmission, ConfirmPublicSubmission, PublicSubmissionTest

### Community 15 - "package.json"
Cohesion: 0.10
Nodes (24): devDependencies, autoprefixer@^10.4.2, concurrently@^10.0.3, laravel-vite-plugin@^3.1, postcss@^8.4.31, tailwindcss@^3.1.0, @tailwindcss/forms@^0.5.2, vite@^8.0.0 (+16 more)

### Community 17 - "Media"
Cohesion: 0.12
Nodes (5): MediaLibraryController, MediaController, AuditLog, Media, MediaUploader

### Community 20 - "ReviewStatus"
Cohesion: 0.10
Nodes (10): ReviewStatus, Accepted, Cancelled, Completed, Declined, Invited, {closure#2}(), {closure#5}() (+2 more)

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.14
Nodes (4): AnnouncementController, NotificationController, ProfileController, ReviewerController

### Community 23 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.10
Nodes (4): HomeBlock, PageRevision, {closure#1}(), UserRole

### Community 24 - "Illuminate\Database\Eloquent\Builder"
Cohesion: 0.17
Nodes (11): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), {closure#9}() (+3 more)

### Community 25 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+6 more)

### Community 29 - "ArticleStat"
Cohesion: 0.17
Nodes (3): ArticleController, ArticleStat, Citation

### Community 30 - "ICSMI"
Cohesion: 0.10
Nodes (18): Backup, Backup dan restore, Batas upload, Batasan yang diketahui, Deploy ke hosting, ICSMI, Konten situs, Menjalankan di komputer lokal (+10 more)

### Community 33 - "ManuscriptStatus"
Cohesion: 0.14
Nodes (10): ManuscriptStatus, Accepted, InReview, Published, Rejected, RevisionRequired, Screening, Submitted (+2 more)

### Community 36 - "MenuItem"
Cohesion: 0.12
Nodes (6): MenuItemController, {closure#1}(), {closure#2}(), {closure#3}(), MenuItem, MarkdownSections

### Community 37 - ".record"
Cohesion: 0.17
Nodes (3): SidebarSectionController, EditorController, SidebarSection

### Community 38 - "Article"
Cohesion: 0.18
Nodes (6): ArticleController, {closure#1}(), {closure#2}(), Article, ArticlePublisher, {closure#1}()

### Community 41 - "ArticleFactory.php"
Cohesion: 0.14
Nodes (3): ArticleFactory, IssueFactory, UserFactory

### Community 43 - "ManuscriptSubmitted"
Cohesion: 0.16
Nodes (5): ManuscriptSubmitted, ReviewInvitation, {closure#3}(), {closure#10}(), {closure#11}()

### Community 44 - "Announcement"
Cohesion: 0.17
Nodes (6): Announcement, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 45 - "Closure"
Cohesion: 0.08
Nodes (10): CountVisits, EnsureUserHasRole, SecurityHeaders, PageVisit, {closure#1}(), VisitCounter, CountryFlag, {closure#1}() (+2 more)

### Community 46 - "Illuminate\Contracts\Validation\Validator"
Cohesion: 0.05
Nodes (16): HomeBlockController, {closure#3}(), {closure#4}(), {closure#5}(), HomeBlockRequest, {closure#1}(), {closure#2}(), MenuItemRequest (+8 more)

### Community 48 - "Manuscript.php"
Cohesion: 0.40
Nodes (3): {closure#1}(), {closure#2}(), ManuscriptAuthor

### Community 49 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 52 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 54 - "ManuscriptWorkflowTest.php"
Cohesion: 0.29
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 56 - "SidebarSection.php"
Cohesion: 0.23
Nodes (3): {closure#1}(), {closure#2}(), SafeMarkdown

### Community 61 - "ReviewerController.php"
Cohesion: 0.29
Nodes (5): ReviewRecommendation, Accept, MajorRevision, MinorRevision, Reject

### Community 62 - "ReviewAssignment"
Cohesion: 0.20
Nodes (4): {closure#2}(), ReviewAssignment, ReviewUpdated, ReviewAssignmentPolicy

### Community 64 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 65 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 66 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 70 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 71 - "2026_11_04_000001_connect_public_submissions_to_manuscripts.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 74 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 75 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 76 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 77 - "require"
Cohesion: 0.40
Nodes (5): require, laravel/framework, laravel/tinker, openspout/openspout, php

### Community 88 - "User"
Cohesion: 0.09
Nodes (8): Role, Admin, Author, Editor, Reviewer, User, {closure#1}(), {closure#2}()

### Community 89 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 91 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 98 - "author/show.blade.php"
Cohesion: 0.50
Nodes (3): manuscripts.files, manuscripts.summary, manuscripts.upload-fields

### Community 99 - "public-layout.blade.php"
Cohesion: 0.50
Nodes (3): site.partials.footer, site.partials.header, site.partials.sidebar

### Community 101 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 102 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **72 isolated node(s):** `admin.partials-tabs`, `editor.partials.tabs`, `editor.partials.tabs`, `manuscripts.table`, `manuscripts.files` (+67 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 758 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **319 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `MenuAndHomeTest.php`, `ContentImporter`, `Illuminate\Http\RedirectResponse`, `Illuminate\Database\Eloquent\Model`, `Manuscript`, `ProfileTabsAndBellTest`, `PublicSubmissionTest.php`, `PublicSubmissionTest`, `SecurityHardeningTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `ReviewStatus`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Database\Eloquent\Builder`, `AnnouncementsAndContentTest`, `Page`, `MediaLibraryTest`, `ArticlePublisher.php`, `MenuAndHomeTest.php`, `App\Http\Controllers\Admin\UserController`, `Illuminate\Contracts\Validation\Validator`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest.php`, `ReviewAssignment`?**
  _High betweenness centrality (0.109) - this node is a cross-community bridge._
- **What connects `admin.partials-tabs`, `editor.partials.tabs`, `editor.partials.tabs` to the rest of the system?**
  _72 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.058001397624039136 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `TestCase` to `StatusBadgeTest`, `AppServiceProvider.php`, `ProfileTabsAndBellTest`, `PublicSubmissionTest.php`, `PublicSubmissionTest`, `SecurityHardeningTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `Risiko dan penanganannya`, `AnnouncementsAndContentTest`, `MediaLibraryTest`, `SecurityFoundationTest`, `MenuAndHomeTest.php`, `PublicSiteTest`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest.php`, `VisitorStatsTest`, `User`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Should `MenuAndHomeTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.09655172413793103 - nodes in this community are weakly interconnected._
- **Why does `Role` connect `User` to `TestCase`, `MenuAndHomeTest.php`, `Illuminate\Http\RedirectResponse`, `Illuminate\Database\Eloquent\Model`, `Manuscript`, `ProfileTabsAndBellTest`, `PublicSubmissionTest.php`, `PublicSubmissionTest`, `SecurityHardeningTest.php`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `ReviewStatus`, `Risiko dan penanganannya`, `AnnouncementsAndContentTest`, `web.php`, `SecurityFoundationTest`, `MenuAndHomeTest.php`, `App\Http\Controllers\Admin\UserController`, `Closure`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest.php`, `ReviewAssignment`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Should `ReportBuilder` be split into smaller, more focused modules?**
  _Cohesion score 0.1310344827586207 - nodes in this community are weakly interconnected._