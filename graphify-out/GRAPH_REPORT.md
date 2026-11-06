# Graph Report - icsmi-baru  (2026-11-07)

## Corpus Check
- 303 files · ~50,726 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 16 file(s) not represented in the graph (top: (none) 11, .example 1, .xml 1)

## Summary
- 1930 nodes · 3855 edges · 387 communities (55 shown, 332 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 100 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ab9ba320`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- MenuAndHomeTest.php
- App\Http\Controllers\ReportController
- ContentImporter
- Illuminate\Http\RedirectResponse
- User
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Model
- Manuscript
- ProfileTabsAndBellTest
- Illuminate\View\View
- App\Services\ReportBuilder
- PublicSubmissionTest
- PublicSubmissionTest.php
- MenuAndHomeTest
- package.json
- AdminAndReportsTest
- Media
- ManuscriptStatus
- PagesAndRevisionsTest
- ReviewStatus
- PublicSubmission
- Illuminate\Http\Request
- HomeBlock
- Illuminate\Database\Eloquent\Builder
- Illuminate\Database\Schema\Blueprint
- AnnouncementsAndContentTest
- web.php
- Page
- ReportBuilder
- ICSMI
- MediaLibraryTest
- SecurityFoundationTest
- ManuscriptStatus
- .record
- SafeLink
- MenuItem
- SidebarSection
- Article
- RegistrationFormTest
- Illuminate\Database\Eloquent\Relations\BelongsTo
- ArticleFactory.php
- VisitCounter.php
- ManuscriptSubmitted
- Announcement
- Closure
- Illuminate\Contracts\Validation\Validator
- PublicSiteTest
- VisitCounter
- Illuminate\Support\Facades\Schema
- ContentImportValidator.php
- MenuItemRequest
- Illuminate\Database\Migrations\Migration
- ManuscriptWorkflowTest.php
- EditorController.php
- SidebarSection.php
- Illuminate\Database\Eloquent\Relations\HasMany
- bootstrap/app.php
- PageRevision
- BoardMember
- ReviewerController.php
- ReviewAssignment
- PageSlug
- composer.json
- require-dev
- scripts
- VisitorStatsTest
- ContentExporter.php
- HomeBlockRequest
- config
- 2026_11_04_000001_connect_public_submissions_to_manuscripts.php
- MenuItem.php
- UserRole
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
- Illuminate\Foundation\Http\FormRequest
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
- ImageReencoder
- ProfileController.php
- .confirm
- StatusBadgeTest
- 2026_11_14_000002_create_notifications_table.php

## God Nodes (most connected - your core abstractions)
1. `User` - 107 edges
2. `Controller` - 66 edges
3. `Role` - 65 edges
4. `Manuscript` - 65 edges
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
- `{closure#11}()` --references--> `ManuscriptStatusChanged`  [EXTRACTED]
  tests/Feature/ManuscriptWorkflowTest.php → app/Notifications/ManuscriptStatusChanged.php
- `ManuscriptWorkflowTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ManuscriptWorkflowTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (387 total, 332 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.06
Nodes (10): AuditFixesTest, AuthenticationTest, EmailVerificationTest, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, InformationTableTest (+2 more)

### Community 1 - "MenuAndHomeTest.php"
Cohesion: 0.06
Nodes (8): DatabaseSeeder, HomeBlockSeeder, MenuSeeder, PageSeeder, SampleAccountSeeder, SampleManuscriptSeeder, SampleProceedingSeeder, SidebarSectionSeeder

### Community 2 - "App\Http\Controllers\ReportController"
Cohesion: 0.16
Nodes (4): {closure#1}(), {closure#2}(), ReportController, ManuscriptFileController

### Community 3 - "ContentImporter"
Cohesion: 0.18
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), ContentImporter

### Community 4 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.09
Nodes (10): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController (+2 more)

### Community 5 - "User"
Cohesion: 0.07
Nodes (4): {closure#3}(), {closure#1}(), {closure#5}(), User

### Community 6 - "AppServiceProvider.php"
Cohesion: 0.06
Nodes (9): AppServiceProvider, {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), PublicUrlTest (+1 more)

### Community 7 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.20
Nodes (6): ArticleAuthor, Keyword, {closure#1}(), {closure#2}(), ManuscriptAuthor, PageVisit

### Community 8 - "Manuscript"
Cohesion: 0.11
Nodes (7): AuthorManuscriptController, Manuscript, ManuscriptPolicy, {closure#1}(), {closure#4}(), {closure#5}(), ManuscriptWorkflow

### Community 9 - "ProfileTabsAndBellTest"
Cohesion: 0.09
Nodes (3): HeroImageUploadTest, ProfileTabsAndBellTest, SidebarEditorTest

### Community 10 - "Illuminate\View\View"
Cohesion: 0.09
Nodes (6): UserController, PublicSubmissionController, PageController, ProceedingController, AppLayout, GuestLayout

### Community 11 - "App\Services\ReportBuilder"
Cohesion: 0.11
Nodes (17): {closure#1}(), {closure#10}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#17}(), {closure#18}(), {closure#19}() (+9 more)

### Community 13 - "PublicSubmissionTest.php"
Cohesion: 0.08
Nodes (6): {closure#2}(), {closure#3}(), ConfirmPublicSubmission, PasswordResetTest, foreach(), test_confirmation_mail_to_one_address_is_limited_per_day()

### Community 15 - "package.json"
Cohesion: 0.10
Nodes (24): devDependencies, autoprefixer@^10.4.2, concurrently@^10.0.3, laravel-vite-plugin@^3.1, postcss@^8.4.31, tailwindcss@^3.1.0, @tailwindcss/forms@^0.5.2, vite@^8.0.0 (+16 more)

### Community 17 - "Media"
Cohesion: 0.17
Nodes (4): MediaLibraryController, AuditLog, Media, MediaUploader

### Community 20 - "ReviewStatus"
Cohesion: 0.14
Nodes (8): ReviewStatus, Accepted, Cancelled, Completed, Declined, Invited, {closure#2}(), ManuscriptFilePolicy

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (4): AnnouncementController, ContentController, NotificationController, EditorController

### Community 24 - "Illuminate\Database\Eloquent\Builder"
Cohesion: 0.15
Nodes (12): ArticleController, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+4 more)

### Community 25 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+6 more)

### Community 28 - "Page"
Cohesion: 0.13
Nodes (6): PageController, Page, {closure#1}(), {closure#2}(), {closure#3}(), PageEditor

### Community 30 - "ICSMI"
Cohesion: 0.10
Nodes (18): Backup, Backup dan restore, Batas upload, Batasan yang diketahui, Deploy ke hosting, ICSMI, Konten situs, Menjalankan di komputer lokal (+10 more)

### Community 32 - "SecurityFoundationTest"
Cohesion: 0.09
Nodes (3): Risiko dan penanganannya, SecurityFoundationTest, SecurityHardeningTest

### Community 33 - "ManuscriptStatus"
Cohesion: 0.11
Nodes (12): ManuscriptStatus, Accepted, InReview, Published, Rejected, RevisionRequired, Screening, Submitted (+4 more)

### Community 36 - "MenuItem"
Cohesion: 0.15
Nodes (3): MenuItemController, MenuItem, MarkdownSections

### Community 38 - "Article"
Cohesion: 0.07
Nodes (11): ArticleController, {closure#1}(), {closure#2}(), StoreManuscriptRequest, Article, ArticleStat, ManuscriptDocument, ArticlePublisher (+3 more)

### Community 41 - "ArticleFactory.php"
Cohesion: 0.14
Nodes (3): ArticleFactory, IssueFactory, UserFactory

### Community 43 - "ManuscriptSubmitted"
Cohesion: 0.18
Nodes (3): ManuscriptSubmitted, ReviewUpdated, {closure#3}()

### Community 45 - "Closure"
Cohesion: 0.23
Nodes (3): MediaController, EnsureUserHasRole, SecurityHeaders

### Community 46 - "Illuminate\Contracts\Validation\Validator"
Cohesion: 0.23
Nodes (4): {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}()

### Community 49 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 51 - "MenuItemRequest"
Cohesion: 0.27
Nodes (3): {closure#1}(), {closure#2}(), MenuItemRequest

### Community 54 - "ManuscriptWorkflowTest.php"
Cohesion: 0.29
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 55 - "EditorController.php"
Cohesion: 0.22
Nodes (4): {closure#2}(), ReviewInvitation, {closure#10}(), {closure#11}()

### Community 56 - "SidebarSection.php"
Cohesion: 0.18
Nodes (4): {closure#1}(), {closure#2}(), {closure#2}(), SafeMarkdown

### Community 58 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 61 - "ReviewerController.php"
Cohesion: 0.25
Nodes (5): ReviewRecommendation, Accept, MajorRevision, MinorRevision, Reject

### Community 62 - "ReviewAssignment"
Cohesion: 0.27
Nodes (3): ReviewerController, ReviewAssignment, ReviewAssignmentPolicy

### Community 64 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 65 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 66 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 68 - "ContentExporter.php"
Cohesion: 0.25
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), ContentExporter

### Community 70 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 71 - "2026_11_04_000001_connect_public_submissions_to_manuscripts.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 72 - "MenuItem.php"
Cohesion: 0.43
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

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
Cohesion: 0.10
Nodes (7): Role, Admin, Author, Editor, Reviewer, {closure#1}(), {closure#2}()

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
- **72 isolated node(s):** `site.partials.menu-item`, `admin.partials-tabs`, `editor.partials.tabs`, `editor.partials.tabs`, `manuscripts.table` (+67 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 757 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **332 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `MenuAndHomeTest.php`, `ContentImporter`, `Manuscript`, `ProfileTabsAndBellTest`, `Illuminate\View\View`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `ReviewStatus`, `AnnouncementsAndContentTest`, `Page`, `MediaLibraryTest`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest.php`, `EditorController.php`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PageRevision`, `ReviewAssignment`, `Role`, `Illuminate\Foundation\Http\FormRequest`, `.confirm`?**
  _High betweenness centrality (0.121) - this node is a cross-community bridge._
- **What connects `site.partials.menu-item`, `admin.partials-tabs`, `editor.partials.tabs` to the rest of the system?**
  _72 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.06274509803921569 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `TestCase` to `StatusBadgeTest`, `MenuAndHomeTest.php`, `AppServiceProvider.php`, `ProfileTabsAndBellTest`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `AnnouncementsAndContentTest`, `MediaLibraryTest`, `SecurityFoundationTest`, `RegistrationFormTest`, `PublicSiteTest`, `AdminAndReportsTest.php`, `ManuscriptWorkflowTest.php`, `VisitorStatsTest`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **Should `MenuAndHomeTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.05858585858585859 - nodes in this community are weakly interconnected._
- **Why does `MenuAndHomeTest` connect `MenuAndHomeTest` to `TestCase`, `MenuAndHomeTest.php`, `User`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.09103840682788052 - nodes in this community are weakly interconnected._