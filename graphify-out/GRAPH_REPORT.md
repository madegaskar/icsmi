# Graph Report - icsmi-baru  (2026-11-07)

## Corpus Check
- 316 files · ~52,180 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 16 file(s) not represented in the graph (top: (none) 11, .example 1, .xml 1)

## Summary
- 1924 nodes · 3974 edges · 332 communities (54 shown, 278 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 100 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `aa7250d0`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Illuminate\Database\Seeder
- ReportBuilder
- ContentImporter
- Illuminate\Http\RedirectResponse
- MenuSeeder
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Model
- Manuscript
- ProfileTabsAndBellTest
- Illuminate\View\View
- EmailVerificationTest.php
- PublicSubmissionTest
- PublicSubmissionTest.php
- MenuAndHomeTest
- package.json
- AdminAndReportsTest
- User
- Media
- PagesAndRevisionsTest
- ReviewAssignment
- Risiko dan penanganannya
- App\Http\Controllers\ProfileController
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Database\Eloquent\Builder
- Illuminate\Database\Schema\Blueprint
- AnnouncementsAndContentTest
- Page
- Article
- ICSMI
- MediaLibraryTest
- SecurityFoundationTest
- ManuscriptStatus
- RegistrationAccessTest
- AppServiceProvider
- MenuItem
- .record
- ManuscriptFile
- ManuscriptStatusChanged
- AuditFixesTest.php
- Illuminate\Support\Str
- Illuminate\Http\RedirectResponse
- PageSlug
- ContentImportValidator.php
- Closure
- HomeBlock
- PublicSiteTest
- Illuminate\Database\Eloquent\Relations\HasMany
- Illuminate\Support\Facades\Schema
- PageEditor
- App\Models\User
- Illuminate\Database\Migrations\Migration
- AdminAndReportsTest.php
- SafeLink
- 2026_11_14_000002_create_notifications_table.php
- SidebarSection.php
- ManuscriptWorkflowTest
- MenuItemRequest
- Illuminate\Contracts\Validation\Validator
- BoardMember
- ReviewerController.php
- .for
- SidebarSectionRequest
- composer.json
- require-dev
- scripts
- MenuAndHomeTest.php
- App\Http\Controllers\Admin\ContentController
- ContentExporter.php
- config
- 2026_11_04_000001_connect_public_submissions_to_manuscripts.php
- VisitorStatsTest
- SampleProceedingSeeder.php
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
- Role
- Laravel Application
- Tests\TestCase
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
- 2026_11_03_000002_create_role_user_table.php
- navigation.blade.php
- SidebarEditorTest.php
- admin.partials-tabs
- ImageReencoder
- Illuminate\View\Component
- RegistrationFormTest
- account-controls.blade.php
- StatusBadgeTest
- ManuscriptDocument

## God Nodes (most connected - your core abstractions)
1. `User` - 106 edges
2. `Manuscript` - 65 edges
3. `Controller` - 64 edges
4. `Role` - 64 edges
5. `ManuscriptStatus` - 55 edges
6. `TestCase` - 54 edges
7. `ManuscriptWorkflowTest` - 45 edges
8. `Article` - 42 edges
9. `Risiko dan penanganannya` - 40 edges
10. `AdminAndReportsTest` - 39 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --references--> `Role`  [EXTRACTED]
  tests/Feature/SecurityFoundationTest.php → app/Enums/Role.php
- `SidebarEditorTest` --references--> `User`  [EXTRACTED]
  tests/Feature/SidebarEditorTest.php → app/Models/User.php
- `PublicSubmissionTest` --references--> `User`  [EXTRACTED]
  tests/Feature/PublicSubmissionTest.php → app/Models/User.php
- `MenuAndHomeTest` --references--> `User`  [EXTRACTED]
  tests/Feature/MenuAndHomeTest.php → app/Models/User.php
- `AdminAndReportsTest` --references--> `User`  [EXTRACTED]
  tests/Feature/AdminAndReportsTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (332 total, 278 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.09
Nodes (8): AuthenticationTest, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, InformationTableTest, ProfileTest, TestCase

### Community 1 - "Illuminate\Database\Seeder"
Cohesion: 0.19
Nodes (4): DatabaseSeeder, PageSeeder, SampleAccountSeeder, SampleManuscriptSeeder

### Community 2 - "ReportBuilder"
Cohesion: 0.12
Nodes (5): {closure#1}(), {closure#2}(), ReportController, ManuscriptFileController, ReportBuilder

### Community 3 - "ContentImporter"
Cohesion: 0.21
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), ContentImporter

### Community 4 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.08
Nodes (10): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, VerifyEmailController (+2 more)

### Community 6 - "AppServiceProvider.php"
Cohesion: 0.17
Nodes (6): {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}()

### Community 7 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.16
Nodes (3): ArticleAuthor, Keyword, StatusHistory

### Community 8 - "Manuscript"
Cohesion: 0.09
Nodes (17): {closure#3}(), EditorController, Manuscript, ManuscriptPolicy, {closure#1}(), ManuscriptWorkflow, {closure#1}(), {closure#10}() (+9 more)

### Community 9 - "ProfileTabsAndBellTest"
Cohesion: 0.09
Nodes (3): HeroImageUploadTest, ProfileTabsAndBellTest, SidebarEditorTest

### Community 10 - "Illuminate\View\View"
Cohesion: 0.09
Nodes (5): AuditLogController, UserController, PageController, ProceedingController, AuthorManuscriptController

### Community 12 - "PublicSubmissionTest"
Cohesion: 0.07
Nodes (8): PublicSubmissionController, {closure#2}(), PublicSubmission, ConfirmPublicSubmission, {closure#1}(), foreach(), PublicSubmissionTest, test_confirmation_mail_to_one_address_is_limited_per_day()

### Community 15 - "package.json"
Cohesion: 0.10
Nodes (24): devDependencies, autoprefixer@^10.4.2, concurrently@^10.0.3, laravel-vite-plugin@^3.1, postcss@^8.4.31, tailwindcss@^3.1.0, @tailwindcss/forms@^0.5.2, vite@^8.0.0 (+16 more)

### Community 17 - "User"
Cohesion: 0.07
Nodes (5): {closure#1}(), {closure#5}(), AuditLog, User, {closure#4}()

### Community 18 - "Media"
Cohesion: 0.19
Nodes (3): MediaLibraryController, Media, MediaUploader

### Community 20 - "ReviewAssignment"
Cohesion: 0.05
Nodes (28): ReviewStatus, Accepted, Cancelled, Completed, Declined, Invited, {closure#2}(), {closure#2}() (+20 more)

### Community 24 - "Illuminate\Database\Eloquent\Builder"
Cohesion: 0.11
Nodes (17): ArticleController, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+9 more)

### Community 25 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+6 more)

### Community 29 - "Article"
Cohesion: 0.09
Nodes (9): ArticleController, {closure#1}(), {closure#2}(), Article, ArticleStat, ArticlePublisher, {closure#1}(), Citation (+1 more)

### Community 30 - "ICSMI"
Cohesion: 0.10
Nodes (18): Backup, Backup dan restore, Batas upload, Batasan yang diketahui, Deploy ke hosting, ICSMI, Konten situs, Menjalankan di komputer lokal (+10 more)

### Community 33 - "ManuscriptStatus"
Cohesion: 0.14
Nodes (9): ManuscriptStatus, Accepted, InReview, Published, Rejected, RevisionRequired, Screening, Submitted (+1 more)

### Community 36 - "MenuItem"
Cohesion: 0.12
Nodes (6): MenuItemController, {closure#1}(), {closure#2}(), {closure#3}(), MenuItem, MarkdownSections

### Community 39 - "ManuscriptStatusChanged"
Cohesion: 0.25
Nodes (4): ManuscriptStatusChanged, {closure#2}(), {closure#6}(), {closure#7}()

### Community 41 - "Illuminate\Support\Str"
Cohesion: 0.10
Nodes (6): IssueController, Issue, {closure#8}(), ArticleFactory, IssueFactory, UserFactory

### Community 42 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.21
Nodes (3): SiteSettingController, RegisteredUserController, SubmissionController

### Community 45 - "Closure"
Cohesion: 0.08
Nodes (9): CountVisits, EnsureUserHasRole, SecurityHeaders, {closure#1}(), VisitCounter, CountryFlag, {closure#1}(), {closure#2}() (+1 more)

### Community 46 - "HomeBlock"
Cohesion: 0.20
Nodes (3): HomeBlockController, HomeBlockRequest, HomeBlock

### Community 48 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.17
Nodes (3): {closure#1}(), {closure#2}(), ManuscriptAuthor

### Community 49 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 50 - "PageEditor"
Cohesion: 0.24
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), PageEditor

### Community 51 - "App\Models\User"
Cohesion: 0.12
Nodes (4): {closure#3}(), {closure#1}(), {closure#1}(), UserRole

### Community 56 - "SidebarSection.php"
Cohesion: 0.20
Nodes (3): {closure#1}(), {closure#2}(), SafeMarkdown

### Community 58 - "MenuItemRequest"
Cohesion: 0.29
Nodes (3): {closure#1}(), {closure#2}(), MenuItemRequest

### Community 59 - "Illuminate\Contracts\Validation\Validator"
Cohesion: 0.23
Nodes (4): {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}()

### Community 61 - "ReviewerController.php"
Cohesion: 0.29
Nodes (5): ReviewRecommendation, Accept, MajorRevision, MinorRevision, Reject

### Community 64 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 65 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 66 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 68 - "App\Http\Controllers\Admin\ContentController"
Cohesion: 0.15
Nodes (3): AnnouncementController, ContentController, NotificationController

### Community 69 - "ContentExporter.php"
Cohesion: 0.27
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), ContentExporter

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

### Community 88 - "Role"
Cohesion: 0.18
Nodes (5): Role, Admin, Author, Editor, Reviewer

### Community 89 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 90 - "Tests\TestCase"
Cohesion: 0.09
Nodes (4): BreadcrumbTest, HeaderAccountTest, MobileHeaderTest, StagingAndPresentationTest

### Community 91 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 98 - "author/show.blade.php"
Cohesion: 0.50
Nodes (3): manuscripts.files, manuscripts.summary, manuscripts.upload-fields

### Community 99 - "public-layout.blade.php"
Cohesion: 0.40
Nodes (4): site.partials.breadcrumb, site.partials.footer, site.partials.header, site.partials.sidebar

### Community 101 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 102 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 388 - "ManuscriptDocument"
Cohesion: 0.14
Nodes (4): ProfileUpdateRequest, StoreManuscriptRequest, ManuscriptDocument, NoControlCharacters

## Knowledge Gaps
- **77 isolated node(s):** `admin.partials-tabs`, `site.partials.menu-item`, `site.partials.account-controls`, `admin.partials-tabs`, `editor.partials.tabs` (+72 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 713 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **278 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `Illuminate\Database\Seeder`, `ContentImporter`, `ManuscriptDocument`, `Manuscript`, `ProfileTabsAndBellTest`, `Illuminate\View\View`, `EmailVerificationTest.php`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `PagesAndRevisionsTest`, `ReviewAssignment`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AnnouncementsAndContentTest`, `web.php`, `Page`, `Article`, `MediaLibraryTest`, `ManuscriptFile`, `Illuminate\Http\RedirectResponse`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PageEditor`, `App\Models\User`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest`, `MenuAndHomeTest.php`, `Role`, `SidebarEditorTest.php`, `RegistrationFormTest`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **What connects `admin.partials-tabs`, `site.partials.menu-item`, `site.partials.account-controls` to the rest of the system?**
  _77 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.08739495798319327 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `TestCase` to `StatusBadgeTest`, `ProfileTabsAndBellTest`, `EmailVerificationTest.php`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `User`, `PagesAndRevisionsTest`, `ReviewAssignment`, `Risiko dan penanganannya`, `AnnouncementsAndContentTest`, `MediaLibraryTest`, `SecurityFoundationTest`, `AuditFixesTest.php`, `PublicSiteTest`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest`, `MenuAndHomeTest.php`, `VisitorStatsTest`, `Tests\TestCase`, `SidebarEditorTest.php`, `RegistrationFormTest`, `PublicSiteTest.php`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Should `ReportBuilder` be split into smaller, more focused modules?**
  _Cohesion score 0.11553030303030302 - nodes in this community are weakly interconnected._
- **Why does `Role` connect `Role` to `TestCase`, `Illuminate\Database\Seeder`, `Illuminate\Database\Eloquent\Model`, `Manuscript`, `ProfileTabsAndBellTest`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `User`, `PagesAndRevisionsTest`, `ReviewAssignment`, `Risiko dan penanganannya`, `AnnouncementsAndContentTest`, `web.php`, `MediaLibraryTest`, `SecurityFoundationTest`, `ManuscriptStatus`, `ManuscriptFile`, `Closure`, `App\Models\User`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest`, `MenuAndHomeTest.php`, `Tests\TestCase`, `SidebarEditorTest.php`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.08095238095238096 - nodes in this community are weakly interconnected._