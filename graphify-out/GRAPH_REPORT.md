# Graph Report - icsmi-baru  (2026-11-07)

## Corpus Check
- 301 files · ~50,536 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 16 file(s) not represented in the graph (top: (none) 11, .example 1, .xml 1)

## Summary
- 1921 nodes · 3844 edges · 385 communities (61 shown, 324 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 100 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0f52aeda`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- MenuAndHomeTest.php
- SubmissionController.php
- ContentImporter
- Illuminate\Http\RedirectResponse
- NewPasswordController.php
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Model
- Manuscript
- ProfileTabsAndBellTest
- Illuminate\View\View
- ArticleController
- PublicSubmissionTest
- PublicSubmissionTest.php
- MenuAndHomeTest
- package.json
- AdminAndReportsTest
- Media
- ManuscriptStatus
- PagesAndRevisionsTest
- ReviewStatus
- SubmissionController.php
- Illuminate\Http\Request
- HomeBlock
- Illuminate\Database\Eloquent\Builder
- Illuminate\Database\Schema\Blueprint
- AnnouncementsAndContentTest
- web.php
- Page
- Controller
- ICSMI
- MediaLibraryTest
- SecurityFoundationTest
- ArticlePublisher.php
- .record
- SafeLink
- MenuItem.php
- SidebarSection
- ArticleStat
- User
- PageEditor
- ArticleFactory.php
- ManuscriptDocument
- ManuscriptSubmitted
- Announcement
- Closure
- Illuminate\Contracts\Validation\Validator
- PublicSiteTest
- Illuminate\Support\Str
- Illuminate\Support\Facades\Schema
- Risiko dan penanganannya
- MenuItemRequest
- Illuminate\Support\Facades\Schema
- Role
- ManuscriptWorkflowTest.php
- EditorController.php
- SidebarSection.php
- Illuminate\Database\Eloquent\Relations\HasMany
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
- Editorial/ArticleController.php
- config
- 2026_11_04_000001_connect_public_submissions_to_manuscripts.php
- EmailVerificationTest.php
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
- Illuminate\Support\Facades\Storage
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
- {closure#1}
- StatusBadgeTest

## God Nodes (most connected - your core abstractions)
1. `User` - 107 edges
2. `Controller` - 66 edges
3. `Role` - 66 edges
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

## Communities (385 total, 324 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.07
Nodes (10): AuditFixesTest, AuthenticationTest, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, InformationTableTest, ProfileTest (+2 more)

### Community 1 - "MenuAndHomeTest.php"
Cohesion: 0.10
Nodes (8): DatabaseSeeder, HomeBlockSeeder, MenuSeeder, PageSeeder, SampleAccountSeeder, SampleManuscriptSeeder, SampleProceedingSeeder, SidebarSectionSeeder

### Community 2 - "SubmissionController.php"
Cohesion: 0.14
Nodes (4): {closure#1}(), {closure#2}(), ReportController, ReportBuilder

### Community 3 - "ContentImporter"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), ContentImporter

### Community 4 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.11
Nodes (6): AuthenticatedSessionController, EmailVerificationNotificationController, EmailVerificationPromptController, PasswordController, VerifyEmailController, ReviewerController

### Community 5 - "NewPasswordController.php"
Cohesion: 0.09
Nodes (5): ConfirmablePasswordController, {closure#1}(), NewPasswordController, PasswordResetLinkController, RegisteredUserController

### Community 6 - "AppServiceProvider.php"
Cohesion: 0.10
Nodes (7): AppServiceProvider, {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}()

### Community 7 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (4): ArticleAuthor, Keyword, PageVisit, StatusHistory

### Community 8 - "Manuscript"
Cohesion: 0.11
Nodes (10): {closure#5}(), Manuscript, ManuscriptPolicy, ManuscriptWorkflow, {closure#1}(), {closure#2}(), {closure#21}(), {closure#3}() (+2 more)

### Community 9 - "ProfileTabsAndBellTest"
Cohesion: 0.09
Nodes (3): HeroImageUploadTest, ProfileTabsAndBellTest, SidebarEditorTest

### Community 10 - "Illuminate\View\View"
Cohesion: 0.11
Nodes (6): MenuItemController, PageController, ProceedingController, MenuItem, AppLayout, GuestLayout

### Community 13 - "PublicSubmissionTest.php"
Cohesion: 0.09
Nodes (3): PasswordResetTest, foreach(), test_confirmation_mail_to_one_address_is_limited_per_day()

### Community 15 - "package.json"
Cohesion: 0.10
Nodes (24): devDependencies, autoprefixer@^10.4.2, concurrently@^10.0.3, laravel-vite-plugin@^3.1, postcss@^8.4.31, tailwindcss@^3.1.0, @tailwindcss/forms@^0.5.2, vite@^8.0.0 (+16 more)

### Community 17 - "Media"
Cohesion: 0.14
Nodes (4): MediaLibraryController, AuditLog, Media, MediaUploader

### Community 18 - "ManuscriptStatus"
Cohesion: 0.08
Nodes (16): ManuscriptStatus, Accepted, InReview, Published, Rejected, RevisionRequired, Screening, Submitted (+8 more)

### Community 20 - "ReviewStatus"
Cohesion: 0.18
Nodes (7): ReviewStatus, Accepted, Cancelled, Completed, Declined, Invited, ManuscriptFilePolicy

### Community 21 - "SubmissionController.php"
Cohesion: 0.18
Nodes (5): PublicSubmissionController, {closure#2}(), {closure#3}(), PublicSubmission, ConfirmPublicSubmission

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.19
Nodes (3): ContentController, NotificationController, SubmissionController

### Community 23 - "HomeBlock"
Cohesion: 0.16
Nodes (3): HomeBlockController, HomeBlockRequest, HomeBlock

### Community 24 - "Illuminate\Database\Eloquent\Builder"
Cohesion: 0.13
Nodes (18): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), {closure#9}() (+10 more)

### Community 25 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+6 more)

### Community 29 - "Controller"
Cohesion: 0.12
Nodes (4): UserController, Controller, AuthorManuscriptController, ManuscriptFileController

### Community 30 - "ICSMI"
Cohesion: 0.10
Nodes (18): Backup, Backup dan restore, Batas upload, Batasan yang diketahui, Deploy ke hosting, ICSMI, Konten situs, Menjalankan di komputer lokal (+10 more)

### Community 34 - ".record"
Cohesion: 0.27
Nodes (3): IssueController, Issue, {closure#8}()

### Community 35 - "SafeLink"
Cohesion: 0.18
Nodes (4): NoControlCharacters, SafeLink, {closure#2}(), ContentImportValidator

### Community 36 - "MenuItem.php"
Cohesion: 0.17
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), MarkdownSections

### Community 37 - "SidebarSection"
Cohesion: 0.16
Nodes (3): SidebarSectionController, EditorController, SidebarSection

### Community 38 - "ArticleStat"
Cohesion: 0.17
Nodes (6): ArticleController, Article, ArticleStat, {closure#10}(), {closure#11}(), {closure#9}()

### Community 40 - "PageEditor"
Cohesion: 0.32
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), PageEditor

### Community 41 - "ArticleFactory.php"
Cohesion: 0.14
Nodes (3): ArticleFactory, IssueFactory, UserFactory

### Community 43 - "ManuscriptSubmitted"
Cohesion: 0.19
Nodes (3): ManuscriptSubmitted, ReviewUpdated, {closure#3}()

### Community 45 - "Closure"
Cohesion: 0.08
Nodes (9): CountVisits, EnsureUserHasRole, SecurityHeaders, {closure#1}(), VisitCounter, CountryFlag, {closure#1}(), {closure#2}() (+1 more)

### Community 46 - "Illuminate\Contracts\Validation\Validator"
Cohesion: 0.23
Nodes (4): {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}()

### Community 49 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 51 - "MenuItemRequest"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), MenuItemRequest

### Community 52 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 53 - "Role"
Cohesion: 0.09
Nodes (6): Role, Admin, Author, Editor, Reviewer, {closure#2}()

### Community 54 - "ManuscriptWorkflowTest.php"
Cohesion: 0.29
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 55 - "EditorController.php"
Cohesion: 0.29
Nodes (4): {closure#2}(), ReviewInvitation, {closure#10}(), {closure#11}()

### Community 56 - "SidebarSection.php"
Cohesion: 0.21
Nodes (4): {closure#1}(), {closure#2}(), {closure#2}(), SafeMarkdown

### Community 57 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.18
Nodes (3): {closure#1}(), {closure#2}(), ManuscriptAuthor

### Community 61 - "ReviewerController.php"
Cohesion: 0.20
Nodes (6): ReviewRecommendation, Accept, MajorRevision, MinorRevision, Reject, {closure#2}()

### Community 62 - "ReviewAssignment"
Cohesion: 0.40
Nodes (3): ReviewAssignment, ReviewAssignmentPolicy, {closure#14}()

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
Cohesion: 0.27
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), ContentExporter

### Community 69 - "Editorial/ArticleController.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), UniqueSlug

### Community 70 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 71 - "2026_11_04_000001_connect_public_submissions_to_manuscripts.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 73 - "UserRole"
Cohesion: 0.29
Nodes (3): {closure#3}(), {closure#1}(), UserRole

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
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 751 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **324 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `MenuAndHomeTest.php`, `ContentImporter`, `Illuminate\Http\RedirectResponse`, `NewPasswordController.php`, `Illuminate\Database\Eloquent\Model`, `Manuscript`, `ProfileTabsAndBellTest`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `ReviewStatus`, `SubmissionController.php`, `Illuminate\Http\Request`, `AnnouncementsAndContentTest`, `Page`, `Controller`, `MediaLibraryTest`, `ArticlePublisher.php`, `PageEditor`, `Role`, `ManuscriptWorkflowTest.php`, `Illuminate\Database\Eloquent\Relations\HasMany`, `MenuAndHomeTest.php`, `PageRevision`, `ReviewerController.php`, `ReviewAssignment`, `EmailVerificationTest.php`, `UserRole`, `Illuminate\Support\Facades\Storage`, `Illuminate\Foundation\Http\FormRequest`?**
  _High betweenness centrality (0.103) - this node is a cross-community bridge._
- **What connects `admin.partials-tabs`, `editor.partials.tabs`, `editor.partials.tabs` to the rest of the system?**
  _72 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.06956521739130435 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `TestCase` to `StatusBadgeTest`, `ProfileTabsAndBellTest`, `PublicSubmissionTest`, `PublicSubmissionTest.php`, `MenuAndHomeTest`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `PagesAndRevisionsTest`, `AnnouncementsAndContentTest`, `MediaLibraryTest`, `SecurityFoundationTest`, `User`, `PublicSiteTest`, `Risiko dan penanganannya`, `Role`, `ManuscriptWorkflowTest.php`, `MenuAndHomeTest.php`, `VisitorStatsTest`, `EmailVerificationTest.php`, `Illuminate\Support\Facades\Storage`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **Should `MenuAndHomeTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.09655172413793103 - nodes in this community are weakly interconnected._
- **Why does `Role` connect `Role` to `TestCase`, `MenuAndHomeTest.php`, `NewPasswordController.php`, `Illuminate\Database\Eloquent\Model`, `Manuscript`, `ProfileTabsAndBellTest`, `PublicSubmissionTest.php`, `AdminAndReportsTest`, `Media`, `ManuscriptStatus`, `SubmissionController.php`, `AnnouncementsAndContentTest`, `web.php`, `Controller`, `MediaLibraryTest`, `SecurityFoundationTest`, `User`, `Closure`, `Risiko dan penanganannya`, `ManuscriptWorkflowTest.php`, `MenuAndHomeTest.php`, `ReviewerController.php`, `Illuminate\Support\Facades\Storage`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Should `SubmissionController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.1402116402116402 - nodes in this community are weakly interconnected._