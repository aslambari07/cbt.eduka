# CBT.EDUKA QA notes

## Verified commands

The latest verification run completed with:

```text
php artisan test                 57 tests, 215 assertions passed
vendor/bin/pint --dirty          passed
npm run build                    passed
php artisan view:cache           passed
git diff --check                 passed
```

The test suite uses the MySQL database configured by `.env.testing` because
the available PHP runtime does not provide `pdo_sqlite`.

## Smoke journeys

- Landing, login, and forgot-password pages return HTTP 200.
- A seeded student can log in, see dynamic exam data, and open student exams.
- A student receives HTTP 403 when requesting teacher resources.
- A seeded admin can open the admin dashboard, exam list, question bank, and
  platform settings.
- Student session payloads are key-safe; submission produces a graded result
  and immediate publication when configured.

## Security coverage

Feature tests cover role access, cross-user session access, cross-institution
exam references, unpublished result visibility, answer-key leakage, stale
answer writes, double submission, password reset, and teacher ownership.

## Manual browser matrix

The responsive Blade layouts are intended for 320×700, 360×800, 390×844,
768×1024, 1440×900, and 1920×1080. The mobile exam page uses its own bottom
navigation and question navigator rather than relying on desktop shrinking.

Before production deployment, run a real browser pass against all six sizes
with an authenticated seeded/staging account and inspect console/network logs,
queue workers, mail delivery, PDF rendering, and production cache configuration.
