# CBT.EDUKA database map

The application uses MySQL/MariaDB and keeps organization, question, exam,
session, result, notification, audit, and certificate data in separate tables.
All foreign keys and indexes are created by the migrations in
`database/migrations`.

## Relationship overview

```text
institutions
  ├── users (institution_id)
  │    ├── teacher_profiles / student_profiles
  │    ├── questions (author_id)
  │    └── exams (owner_id)
  ├── subjects
  ├── academic_years
  └── classes
       ├── class_members -> users
       ├── class_teachers -> users + subjects
       └── class_subjects -> subjects

subjects ── questions ── question_options
             │
             └── exam_questions ── exams
                                  ├── exam_assignments ── users
                                  ├── exam_sessions ── users
                                  │                    ├── exam_answers
                                  │                    ├── exam_flags
                                  │                    └── exam_events
                                  └── exam_results ── exam_result_details
                                                       └── certificates
```

## Integrity rules

- `users.email`, institution codes, subject codes per institution, and exam
  codes are unique where the domain requires it.
- Exam sessions are unique by `session_key`; submitted sessions are immutable.
- Exam assignments bind a student to an exam and enforce attempt limits in the
  session service.
- Correct answers live in question definitions/options and are not included in
  participant render payloads.
- Results are published through `ExamPublicationService`; final results are
  not deleted as part of ordinary CRUD.
- Audit logs and notifications are append/read-oriented supporting data.
- Soft deletes are used for questions, exams, institutions, classes, and
  subjects; user deactivation is represented by the account status field.

## Main migrations

| Migration | Responsibility |
| --- | --- |
| `220000` | Institutions, users' organization fields, classes, subjects, memberships |
| `221000` | Question categories, questions, options, tags and media metadata |
| `222000` | Exams, exam-question ordering, assignments |
| `223000` | Sessions, answers, flags, exam events |
| `224000` | Results, result details, certificates |
| `225000` | Laravel database notifications |
| `226000` | Audit log events |
| `227000` | Platform settings |

Use `php artisan migrate:fresh --env=testing --seed` only against the dedicated
test database. Production data should use normal forward migrations and the
idempotent seeder should not be run as a destructive reset.
