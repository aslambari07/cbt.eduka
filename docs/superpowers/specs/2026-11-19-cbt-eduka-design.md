# CBT.EDUKA Full Platform Design

## Intent

Build CBT.EDUKA as a production-oriented Laravel application for institutions, teachers, students, and administrators. The platform must turn the supplied dark editorial CBT.EDUKA screens into real authenticated workflows backed by persistent data, with the exam session and grading path treated as the highest-risk domain.

The application is being created in an empty workspace, so Laravel 13 will be scaffolded as the foundation. The implementation will stay within the existing Laravel/Blade/Vite stack and will not import unrelated code from neighboring projects.

## Goals

- Provide authenticated Admin, Teacher, and Student experiences matching the approved CBT.EDUKA visual system.
- Persist organizations, users, classes, subjects, question banks, exams, assignments, sessions, answers, results, certificates, notifications, and audit events.
- Provide real CRUD, validation, authorization, search/filter/pagination, status feedback, and safe archive behavior for core resources.
- Provide a secure exam engine with server-authoritative timing, session ownership checks, stable randomization, autosave, navigation, flags, idempotent submission, auto-grading, manual essay grading, and result publication.
- Provide responsive desktop dashboards and a dedicated mobile exam layout with bottom-sheet navigation.
- Keep business rules in services/actions and question behavior behind a question-type contract.
- Make demo data idempotent and make the principal flows testable.

## Non-goals for the first implementation pass

- Native mobile applications.
- Proctoring or claims of cheating detection from browser events.
- Automatic publication of AI-generated questions.
- Real-time websocket infrastructure when a polling-based monitoring view is sufficient for the initial release.
- External billing, SSO, and multi-region deployment concerns.

## Anti-goals

- Do not create a static UI that pretends to be connected to a database.
- Do not expose answer keys or scoring metadata to student-facing payloads before publication.
- Do not authorize access through hidden navigation alone.
- Do not place grading, lifecycle transitions, or autosave integrity rules in Blade or oversized controllers.
- Do not copy an unrelated neighboring application into this project.

## Approved approach

### Approach A — new Laravel foundation with vertical slices (selected)

Scaffold Laravel 13 in the empty target directory, then implement the system in vertical slices: authentication/roles, shared design system, core academic data, teacher question bank and exam builder, student exam session, results, and finally administrative/reporting modules. Each slice includes migrations, policy checks, service/action logic, Blade UI, and focused tests. This keeps the app coherent and lets the exam engine be verified before broadening CRUD coverage.

### Approach B — copy a neighboring Laravel project

Rejected because the available neighboring projects are unrelated domains. Reusing their models, routes, or dependencies would create hidden coupling and increase authorization and data-model risk.

### Approach C — frontend-first prototype

Rejected because the request requires persistence, secure sessions, complete CRUD, and server-side authorization. A visual prototype can be extracted from the final Blade components, but it cannot be the implementation foundation.

## Product surfaces

### Public/authentication

- Editorial landing page.
- Login, registration, forgot/reset password, and profile entry points.
- Shared CBT.EDUKA typography, tokens, focus states, and responsive behavior.

### Admin

- Overview metrics sourced from database queries.
- Users, institutions, classes, subjects, exams, questions, reports, audit log, settings.
- Search, filters, pagination, guarded status changes, archive/revoke policies, and export hooks.

### Teacher

- Dashboard and activity summaries.
- Question bank and question editor.
- Exam builder wizard with draft-to-published lifecycle.
- Class/participant views.
- Live monitoring based on valid sessions and recent activity.
- Grading, result publication, analytics, and export hooks.
- AI generator as a separate draft-only module when provider configuration exists; otherwise show honest unavailable/configuration state.

### Student

- Dashboard, assigned exams, history, results, notifications, and certificates.
- Exam entry verification and a dedicated desktop/mobile exam experience.
- Server-backed autosave and submission with clear saving/error/retry states.

## Architecture

Use Laravel MVC with narrow controllers and domain-focused services/actions:

- `app/Enums`: roles, exam status, session status, question types, answer/save states.
- `app/Models`: Eloquent relations, casts, scopes, and domain constants only.
- `app/Http/Requests`: authorization-aware validation for every write endpoint.
- `app/Policies`: resource ownership and role boundaries.
- `app/Services` and `app/Actions`: exam lifecycle, sessions, answers, grading, result calculation, exports, and dashboard queries.
- `app/Contracts` plus `app/QuestionTypes`: question-type handlers with validation, answer normalization, rendering data, and scoring.
- `app/Events`, `app/Listeners`, `app/Jobs`, `app/Notifications`: lifecycle side effects, heavy exports, and user notifications.
- `resources/views`: layouts, reusable Blade components, role areas, and exam-specific responsive templates.
- `resources/js`: small progressive-enhancement modules for exam autosave, timer, navigator, mobile sheet, and form affordances.

Controllers will receive validated requests, resolve authorized models, call one application service/action, and return a view or JSON response. Multi-table writes will be wrapped in transactions. Query-heavy dashboard data will use dedicated query services with eager loading and aggregate queries.

## Core data model

The first schema pass will use these principal entities and foreign-key relationships:

- `users` belongs to an optional `institution`, has a role, and owns a profile.
- `institutions` has many users, classes, subjects, and exams.
- `classes` belongs to an institution and has many students through `class_members` plus teacher assignments.
- `subjects` belongs to an institution and is used by questions and exams.
- `questions` belongs to an author, subject, and optional category; `question_options`, media, and tags extend it.
- `exams` belongs to an owner, subject, and optional class; `exam_questions` stores stable order and points.
- `exam_assignments` links an exam to a student and controls access/attempt policy.
- `exam_sessions` links one assignment to one active/final attempt and stores server start/end, status, and stable question/option order snapshots.
- `exam_answers` belongs to a session and question and stores the normalized response, save version, timestamps, and grading state.
- `exam_flags` and `exam_events` belong to sessions for navigation/audit.
- `exam_results` and detail rows store calculated/final scores and per-question provenance; publication is controlled separately from grading.
- `certificates`, notifications, and audit logs are scoped to their owner/context and are never exposed cross-role.

Indexes will cover role/status searches, foreign keys, exam schedules, session ownership, answer uniqueness, and common dashboard filters. Final results will be corrected or archived through controlled actions with audit entries rather than destructive deletes.

## Authorization and security design

- Authentication middleware protects all role areas.
- Role middleware is a coarse boundary; policies enforce resource ownership and assignment membership.
- Student exam endpoints validate current authenticated user, assignment, session status, server time, and question membership on every request.
- Exam sessions use unique assignment/attempt constraints and transactions to prevent duplicate starts/submissions.
- The server computes remaining time from stored UTC timestamps; browser timers are display-only.
- Autosave accepts only the current session's question and uses an idempotency/request version to reject stale writes.
- Final submission locks the session and answer mutations atomically.
- Randomized order is generated once per session and stored as a snapshot/mapping.
- Correct answers are kept out of student payloads until the configured result publication stage.
- CSRF, mass-assignment protection, HTML sanitization, validation, rate limits, and audit logging are required for write and sensitive read endpoints.

## Question engine

Define a `QuestionTypeHandler` contract and a registry keyed by enum. Handlers cover multiple choice, multiple select, true/false, short answer, essay, matching, ordering, and fill-in-the-blanks as supported by the schema. Each handler supplies editor validation, answer normalization, render data, and scoring behavior. Essay answers remain pending/manual until a teacher grades them with optional rubric criteria. Multiple-select partial scoring will be explicit in the exam configuration and tested.

## UI and design fidelity

Create shared Blade components for sidebar, header, statistic card, table, form field, badge, modal/confirmation, tabs, toast, empty/loading/error states, question card, timer, navigator, and responsive mobile bottom sheet. Use the supplied tokens exactly: obsidian `#050505`, surface `#111111`, elevated `#191919`, border `#292929`, muted `#A1A1AA`, orange `#FF4500`, indigo `#7777F5`, violet `#8B5CF6`, success `#10B981`, warning `#F59E0B`, error `#F43F5E`, info `#38BDF8`. Inter, Playfair Display, and JetBrains Mono remain the type pairing.

The exam page has two intentional layouts: desktop with question content and navigator side-by-side, and mobile with compact header, progress strip, large touch targets, fixed bottom actions, and a bottom-sheet question navigator. Decorative effects stay restrained outside the landing/auth visuals and never interfere with exam focus.

## Delivery phases

1. Scaffold Laravel and establish environment, shared tokens, base layout, and authentication.
2. Add role model, policies, institution/class/subject foundations, seed data, and role dashboards.
3. Add questions, question handlers, teacher question bank, and exam builder.
4. Add assignments, sessions, server timer, stable randomization, autosave, flags, navigation, and submission.
5. Add auto/manual grading, results, publication, analytics, notifications, certificates, and exports.
6. Add admin CRUD/reporting, AI draft integration boundary, and remaining management screens.
7. Run unit, feature, security, browser, responsive, and performance verification; fix blockers before making readiness claims.

## Verification strategy

- Unit tests pin handler normalization/scoring, exam lifecycle transitions, timer calculation, and result calculations.
- Feature tests cover auth, role access, CRUD writes, session creation, autosave, stale-write rejection, submission idempotency, grading, publication, and certificate ownership.
- Browser checks cover the three role journeys, exam interaction, autosave indicators, mobile navigation, responsive widths, and console/network errors where browser tooling is available.
- The final report will separate implemented, partial, blocked, and unverified areas; production readiness will not be claimed while exam integrity, authorization, persistence, or required tests remain incomplete.

## Assumptions and decisions to validate during implementation

- Laravel 13 is available for the configured PHP 8.3 environment; if the package is unavailable, use the newest compatible Laravel release and record the deviation.
- MySQL is the target relational database; SQLite may be used for fast isolated tests if schema behavior remains compatible.
- Blade plus Vite/Tailwind is the frontend foundation; no SPA rewrite is introduced.
- AI is optional and must surface configuration status honestly until a provider key/configuration exists.
- The supplied sample dates and metrics are visual reference data only; production dashboards must query persisted records.
