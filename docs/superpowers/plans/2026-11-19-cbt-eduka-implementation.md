# CBT.EDUKA Full Platform Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Laravel-based CBT.EDUKA platform with the supplied dark editorial UI, persistent role-aware CRUD, and a server-authoritative exam engine.

**Architecture:** Scaffold Laravel 13 in the empty target and keep Blade/Vite as the UI foundation. Use enums, policies, form requests, services/actions, question-type handlers, events/jobs, and focused Blade components; controllers stay thin and exam state changes occur inside transactions.

**Tech Stack:** PHP 8.3, Laravel 13 (or the newest compatible release if unavailable), MySQL/MariaDB, Blade, Vite, Tailwind CSS, PHPUnit/Pest as provided by the scaffold, and browser verification with Chrome/Playwright when available.

**Spec:** `docs/superpowers/specs/2026-11-19-cbt-eduka-design.md`

## Global Constraints

- Preserve the supplied CBT.EDUKA visual tokens: obsidian `#050505`, surface `#111111`, elevated `#191919`, border `#292929`, muted `#A1A1AA`, orange `#FF4500`, indigo `#7777F5`, violet `#8B5CF6`, success `#10B981`, warning `#F59E0B`, error `#F43F5E`, info `#38BDF8`.
- Keep Inter for UI/body, Playfair Display for editorial headings, and JetBrains Mono for exam metadata, timers, and codes.
- Do not copy unrelated neighboring projects into this workspace.
- Controllers receive validated input, enforce policies, call actions/services, and return responses; business rules do not live in Blade or oversized controllers.
- All sensitive writes use CSRF, request validation, mass-assignment protection, authorization, and transactions where multiple records change.
- Student exam payloads never include answer keys before the configured publication stage.
- Exam time is derived from server timestamps; browser countdowns are display-only.
- Do not claim production readiness until fresh verification evidence covers persistence, authorization, exam integrity, and relevant tests.

## Review Focus

- A stale or out-of-order autosave must not overwrite a newer answer; pin this in the answer service feature test.
- A student must not read or mutate another student's session, answer, result, or certificate; pin this in policy and HTTP feature tests.
- A second start or submission request must be idempotent and must not create duplicate sessions/results; pin this in exam session feature tests.
- An expired session must reject answer writes and finalize through one atomic path; pin this in timer/submission tests.
- A teacher must not mutate another teacher's question/exam/class; pin this in teacher policy tests.

---

### Task 1: Scaffold Laravel and establish the development baseline

**Files:**
- Create: Laravel 13 application files in the repository root via Composer.
- Modify: `.env.example`, `composer.json`, `package.json`, `vite.config.js`, `phpunit.xml` as required by the scaffold.
- Create: `README.md` setup and verification notes.
- Test: scaffold smoke test and the default application test suite.

**Interfaces:**
- Produces a runnable `artisan`, Composer autoload, Vite entrypoint, application key configuration, and test database configuration for later tasks.

- [ ] **Step 1: Verify the target directory is empty and record available versions**

Run `php -v`, `composer --version`, `node --version`, `npm --version`, and confirm MySQL availability.

- [ ] **Step 2: Scaffold the application**

Run `composer create-project laravel/laravel:^13.0 .`; if Composer reports that Laravel 13 is unavailable for PHP 8.3, use the newest compatible Laravel release and record the deviation in `README.md`.

- [ ] **Step 3: Configure local and test environment defaults**

Set application URL, database driver placeholders, cache/session/queue defaults, mail log driver, and test database settings without adding secrets.

- [ ] **Step 4: Run the scaffold verification**

Run `php artisan about`, `php artisan migrate --env=testing`, and the default test command. Expected: commands exit successfully and the default test suite passes.

- [ ] **Step 5: Document setup and commit the scaffold baseline**

Document database creation, Composer/npm commands, seeded demo accounts once available, and verification commands.

### Task 2: Build the design system, layouts, and authentication shell

**Files:**
- Create: `resources/css/app.css`, `resources/js/app.js`, and shared Blade components under `resources/views/components/`.
- Create: `resources/views/layouts/app.blade.php`, `resources/views/layouts/auth.blade.php`, `resources/views/layouts/dashboard.blade.php`, and public/auth views.
- Modify: `routes/web.php` and authentication routes/controllers provided by the scaffold.
- Test: feature tests for guest/authenticated access and password visibility interaction where browser tooling exists.

**Interfaces:**
- Produces `x-app-layout`, `x-auth-layout`, `x-sidebar`, `x-topbar`, `x-stat-card`, `x-badge`, `x-button`, `x-form-field`, `x-empty-state`, `x-loading-state`, `x-error-state`, `x-modal`, and `x-toast` components.

- [ ] **Step 1: Add the failing auth access tests**

Cover guest redirect to login, authenticated redirect to role landing page, login validation, logout invalidation, and CSRF-protected form submission.

- [ ] **Step 2: Implement the base tokens and shared layouts**

Move the supplied tokens, typography, focus rings, border/radius scale, responsive breakpoints, and restrained micro-interactions into reusable CSS/Tailwind configuration.

- [ ] **Step 3: Implement login, registration, password reset, and profile shell**

Use Laravel's native auth primitives where available; keep the supplied login/signup visual structure and ensure validation errors appear under their fields.

- [ ] **Step 4: Run auth tests and Vite build**

Run `php artisan test --filter=Authentication` and `npm run build`. Expected: tests pass and Vite emits production assets.

### Task 3: Add identity, organization, roles, policies, and seed data

**Files:**
- Create: migrations for institutions, profiles, role fields/permissions, classes, class members, teacher assignments, subjects, academic years, and notifications/audit prerequisites.
- Create: `app/Enums/Role.php`, `app/Enums/AccountStatus.php`, relevant models, factories, policies, middleware, and seeders.
- Create: `database/seeders/DatabaseSeeder.php` demo graph with idempotent `updateOrCreate` behavior.
- Test: role middleware, policy, and ownership feature tests.

**Interfaces:**
- Produces `User::hasRole(Role|string)`, `User::isActive()`, `ClassPolicy`, `SubjectPolicy`, `InstitutionPolicy`, `role` middleware, and seeded admin/teacher/student accounts.

- [ ] **Step 1: Write failing migration/model relationship tests**

Assert role/status casting, institution membership, class membership, teacher assignments, subject ownership, and unique membership constraints.

- [ ] **Step 2: Implement migrations and models**

Use foreign keys, indexes, unique constraints, timestamps, and soft deletes only on institutions/classes/subjects where archive semantics are appropriate.

- [ ] **Step 3: Implement role middleware and policies**

Use server-side role checks plus ownership/institution checks; never use hidden navigation as authorization.

- [ ] **Step 4: Implement idempotent factories and seeders**

Create clearly marked local demo identities and basic institution/class/subject relationships without deleting existing records.

- [ ] **Step 5: Run migrations, seeders, and authorization tests**

Run `php artisan migrate:fresh --env=testing --seed` and the focused policy suite. Expected: constraints apply, seeding is repeatable, and cross-role/cross-owner access is denied.

### Task 4: Implement question bank storage and extensible question handlers

**Files:**
- Create: migrations/models for question categories, questions, options, media, tags, pivots, and grading metadata.
- Create: `app/Enums/QuestionType.php`, `app/Contracts/QuestionTypeHandler.php`, `app/QuestionTypes/*Handler.php`, `app/Services/QuestionTypeRegistry.php`.
- Create: teacher/admin question requests, policies, controllers, actions, and Blade views/components for search/filter/pagination, CRUD, duplicate, archive, and preview.
- Test: handler unit tests and question bank feature/security tests.

**Interfaces:**
- `QuestionTypeHandler::validateDefinition(array $definition): array`
- `QuestionTypeHandler::normalizeAnswer(mixed $answer): mixed`
- `QuestionTypeHandler::score(mixed $answer, array $definition, float $points): ScoreResult`
- `QuestionTypeHandler::renderData(Question $question, bool $includeKey = false): array`
- `QuestionTypeRegistry::for(QuestionType $type): QuestionTypeHandler`

- [ ] **Step 1: Write failing tests for all supported scoring rules**

Cover multiple choice, multiple select with explicit partial-score mode, true/false, normalized short answer, essay pending state, matching, ordering, and fill-in-the-blanks.

- [ ] **Step 2: Implement enums, DTO/result objects, registry, and handlers**

Keep correct answers in server-side definition data and make render data omit them unless the caller is authorized and publication rules allow them.

- [ ] **Step 3: Implement migrations/models and validation requests**

Sanitize rich text, validate options and answer shape by handler, and protect mass assignment.

- [ ] **Step 4: Implement teacher/admin question bank UI and actions**

Include server-side search, subject/category/type/difficulty filters, pagination, duplicate, archive, and preview states matching the design system.

- [ ] **Step 5: Run handler and feature tests**

Run the focused question suite and verify unauthorized teachers cannot read or mutate another owner's questions.

### Task 5: Implement exam configuration, lifecycle, assignments, and builder

**Files:**
- Create: migrations/models for exams, exam sections, exam questions, exam assignments, and lifecycle metadata.
- Create: `app/Enums/ExamStatus.php`, `app/Services/ExamLifecycleService.php`, `app/Services/ExamBuilderService.php`.
- Create: teacher/admin exam requests, policies, controllers/actions, builder wizard views, preview, list/detail screens, and assignment management.
- Test: lifecycle transition, builder transaction, assignment authorization, and validation feature tests.

**Interfaces:**
- `ExamLifecycleService::transition(Exam $exam, ExamStatus $to, User $actor): Exam`
- `ExamBuilderService::saveDraft(ExamDraftData $data, ?Exam $exam = null): Exam`
- `ExamBuilderService::publish(Exam $exam, User $actor): Exam`

- [ ] **Step 1: Write failing lifecycle and builder tests**

Assert allowed transitions Draft → Scheduled/Published, Published → Ongoing/Completed, controlled archive, invalid transition rejection, required questions before publish, and transaction rollback on invalid assignments.

- [ ] **Step 2: Implement exam schema, enums, models, and policies**

Store duration, UTC schedule, passing grade, attempts, randomization, navigation, results, and scoring configuration explicitly.

- [ ] **Step 3: Implement builder actions and requests**

Build the eight-step flow from the design: basics, subject/class, questions, schedule, security, scoring, preview, publish.

- [ ] **Step 4: Implement teacher/admin exam UI**

Use real data, draft persistence, reorder/remove question actions, filters, pagination, archive confirmation, and lifecycle feedback.

- [ ] **Step 5: Run lifecycle/builder tests and route checks**

Run focused tests plus `php artisan route:list`; expected: transitions and authorization pass and all builder forms map to named routes.

### Task 6: Implement the secure student exam session engine

**Files:**
- Create: migrations/models for exam sessions, answers, flags, and events; add unique indexes for assignment/attempt and session/question answers.
- Create: `app/Enums/ExamSessionStatus.php`, `app/Enums/AnswerSaveState.php`, `app/Services/ExamSessionService.php`, `app/Services/AnswerAutosaveService.php`, `app/Services/ExamSubmissionService.php`, `app/Services/ExamTimerService.php`.
- Create: student exam controllers/requests/policies, desktop and mobile Blade views, exam JS modules, and JSON endpoints.
- Test: session integrity, timer, randomization, autosave ordering/idempotency, flag/navigation, expiry, and submission feature tests.

**Interfaces:**
- `ExamSessionService::start(User $student, ExamAssignment $assignment): ExamSession`
- `ExamSessionService::snapshot(ExamSession $session, User $student): ExamSnapshot`
- `AnswerAutosaveService::save(ExamSession $session, Question $question, mixed $answer, string $requestId, int $clientVersion): SavedAnswer`
- `ExamSubmissionService::submit(ExamSession $session, User $student, SubmissionReason $reason): ExamSubmissionResult`
- `ExamTimerService::remainingSeconds(ExamSession $session, CarbonImmutable $now): int`

- [ ] **Step 1: Write failing security and engine tests**

Cover unauthorized session access, duplicate start, stable randomized order, server-time remaining seconds, stale answer rejection, connection retry/idempotency, expired write rejection, double submit, and answer-key omission.

- [ ] **Step 2: Implement schema and session creation**

Create the session in a transaction, lock the assignment where needed, persist question/option order snapshot, and record the start event.

- [ ] **Step 3: Implement server-authoritative timer and autosave**

Validate session ownership/status on every request, compare request versions, store normalized answers, and return explicit saving/saved/error JSON states.

- [ ] **Step 4: Implement submission and expiry finalization**

Lock the session, reject further writes, persist finish metadata, calculate objective scores, leave essay answers pending, and make repeat submission return the existing result.

- [ ] **Step 5: Implement desktop/mobile exam UI**

Match the supplied desktop layout and dedicated mobile interface with fixed bottom actions, touch-sized options, bottom-sheet navigator, flags, progress filters, save indicator, timer warnings, confirmation dialog, and reduced-motion behavior.

- [ ] **Step 6: Run the full exam-engine test suite**

Run the focused feature tests and manually exercise the endpoints with a seeded student. Expected: answers persist across reload, stale requests do not overwrite newer answers, and final submission is one-way.

### Task 7: Implement grading, results, publication, analytics, and certificates

**Files:**
- Create: migrations/models for result summaries/details, grading rubrics, score corrections, certificates, and publication metadata.
- Create: `app/Services/GradingService.php`, `app/Services/ResultCalculationService.php`, `app/Services/CertificateService.php`, teacher grading requests/policies, and result controllers/views.
- Create: student result/history views matching the supplied analytics screen and teacher/admin analytics queries.
- Test: auto-grading, manual essay grading, correction audit, publication visibility, analytics aggregates, and certificate ownership.

**Interfaces:**
- `GradingService::gradeObjective(ExamSession $session): ExamResult`
- `GradingService::gradeEssay(ExamAnswer $answer, EssayGradeData $data, User $grader): ExamAnswer`
- `ResultCalculationService::recalculate(ExamResult $result): ExamResult`
- `CertificateService::issue(ExamResult $result): Certificate`

- [ ] **Step 1: Write failing grading/publication tests**

Assert score provenance, passing grade, pending essays, correction history, unpublished result denial, and student-owned certificate access.

- [ ] **Step 2: Implement result and grading schema/services**

Use decimal scores, per-question detail rows, explicit publication status, and immutable audit records for corrections.

- [ ] **Step 3: Implement teacher grading and student result UIs**

Include breakdowns, review answers, per-type bars, comparison/area metrics, recommendations, manual feedback, and controlled reveal of explanations.

- [ ] **Step 4: Implement certificate issuance and verification**

Use unique certificate numbers, scoped access, and a verification route that exposes only intended public certificate fields.

- [ ] **Step 5: Run grading/results/certificate tests**

Verify database-backed metrics are correct for the seeded graph and no answer key leaks before publication.

### Task 8: Implement role dashboards and core CRUD surfaces

**Files:**
- Create/modify: admin, teacher, and student dashboard controllers/query services and Blade views.
- Create: CRUD routes/controllers/requests/views for users, institutions, classes, subjects, exams, questions, and relevant archive actions.
- Create: reusable table/filter/pagination/confirmation components and flash/toast handling.
- Test: dashboard query tests, CRUD feature tests, authorization tests, search/filter/pagination tests.

**Interfaces:**
- `AdminDashboardQuery::metrics(FilterData $filters): DashboardMetrics`
- `TeacherDashboardQuery::summary(User $teacher): TeacherSummary`
- `StudentDashboardQuery::overview(User $student): StudentOverview`

- [ ] **Step 1: Write failing CRUD and dashboard tests**

Cover create/read/update/archive, invalid input, empty states, search, filter, pagination, sorting, and role ownership for each core module.

- [ ] **Step 2: Implement query services and dashboard controllers**

Use aggregates/eager loading and database-backed values; no static production statistics.

- [ ] **Step 3: Implement admin management surfaces**

Users, institutions, classes, subjects, exams, questions, audit views, and settings hooks with explicit archive/status actions.

- [ ] **Step 4: Implement teacher management surfaces**

Classes, question bank, exams, monitoring, grading entry points, and quick actions with teacher-scoped data.

- [ ] **Step 5: Implement student dashboard/history surfaces**

Upcoming/available/completed exam cards, result history, progress chart data, notification badge, and certificate links.

- [ ] **Step 6: Run CRUD/dashboard tests and inspect generated SQL where needed**

Confirm pagination/search are server-side and no N+1 query is introduced in dashboard lists.

### Task 9: Add notifications, audit, exports, and optional AI integration boundary

**Files:**
- Create: notifications, announcements, audit log models/migrations, listeners/jobs/notifications, export services/jobs, and AI provider contract/configuration.
- Create: notification center views/routes and admin audit views.
- Create: `app/Contracts/AiQuestionProvider.php`, provider adapter stub, generation request/action, and honest unavailable/configured UI state.
- Test: notification read state, audit entries, export authorization, AI configuration behavior, and queued job dispatch.

**Interfaces:**
- `AiQuestionProvider::generate(QuestionGenerationRequest $request): GeneratedQuestionDraftCollection`
- `AuditLogService::record(User $actor, string $event, Model $subject, array $metadata = []): AuditLog`
- `ExamExportJob::handle(ExamExportService $exporter): void`

- [ ] **Step 1: Write failing notification/audit/export/AI tests**

Cover recipient scoping, read/unread updates, audit immutability, export permission, queued dispatch, and no-provider behavior.

- [ ] **Step 2: Implement notification and audit infrastructure**

Connect exam created/scheduled/completed/published/certificate events to notifications and audit entries without leaking sensitive data.

- [ ] **Step 3: Implement export jobs and download authorization**

Start with CSV-safe exports for users, questions, exams, and results; keep heavy work queued and scoped.

- [ ] **Step 4: Implement AI boundary and draft-only generator UI**

Read credentials from environment, apply timeout/error handling, validate provider output, log usage, and require teacher acceptance before persistence/publication.

- [ ] **Step 5: Run integration tests and queue checks**

Run `php artisan queue:work --once` against test fixtures or invoke jobs synchronously in tests; verify audit and notification side effects.

### Task 10: Responsive/browser QA, hardening, documentation, and final verification

**Files:**
- Create: browser test configuration/specs if Playwright is available, `docs/database.md`, `docs/qa.md`, and final implementation report.
- Modify: README, accessibility labels, responsive CSS/JS, error handling, and any files found by QA.
- Test: full PHPUnit/Pest suite, browser journeys, build, lint/static checks, and responsive screenshots where tools support them.

**Interfaces:**
- Produces a documented verification matrix and an honest readiness status, not a claim based on code inspection alone.

- [ ] **Step 1: Add the remaining regression/security tests**

Cover IDOR attempts, role boundaries, XSS sanitization, CSRF, expired sessions, double submission, unpublished results, and archived resource behavior.

- [ ] **Step 2: Run the full backend test suite**

Run the project test command with a clean test database. Record total/passed/failed/skipped counts and fix all blockers in scope.

- [ ] **Step 3: Build assets and run browser journeys**

Verify login, role dashboards, teacher exam creation, student exam start/autosave/submit, teacher grading, student result, and admin management at 320×700, 360×800, 390×844, 768×1024, 1440×900, and 1920×1080.

- [ ] **Step 4: Check accessibility, console errors, failed requests, and N+1 risks**

Confirm labels, focus states, tap targets, no horizontal overflow, no JavaScript console errors, and no failed network requests in the main journeys.

- [ ] **Step 5: Write the final report and readiness decision**

Include executive summary, architecture, CRUD matrix, tests, remaining work, blockers, and choose READY/PARTIALLY READY/NOT READY strictly from the evidence.

## Self-review coverage

- Repository audit and empty-workspace risk are covered by Task 1.
- Authentication and role boundaries are covered by Tasks 2–3 and Task 10.
- CRUD and database persistence are covered by Tasks 3, 4, 5, 8, and 9.
- Question types and scoring are covered by Tasks 4 and 7.
- Exam session integrity, timing, autosave, randomization, and submission are covered by Task 6.
- Results, manual grading, publication, analytics, certificates, notifications, exports, and AI boundary are covered by Tasks 7 and 9.
- Supplied responsive designs and mobile exam-specific behavior are covered by Tasks 2, 6, 8, and 10.
- Final verification and honest readiness reporting are covered by Task 10.
