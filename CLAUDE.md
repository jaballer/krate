# Krate — project guide for AI agents

Krate is a **vinyl record collection manager** built on **Laravel 13 (PHP 8.3+)**
and **Filament 5**. It has two surfaces sharing one `users` table:

- **Public catalog** — read-only, searchable browse/detail (Blade + Tailwind CSS
  v4 + Alpine). Members authenticate via Laravel Breeze.
- **Staff back-office** — Filament admin panel at `/admin` for record/user/setting
  CRUD. Role-gated: only `Administrator`/`Manager` pass `User::canAccessPanel()`.

The original hand-rolled PHP MVC app (Twig/Gulp/Bootstrap) has been fully removed
— do not reintroduce its patterns. There is no `legacy/` directory anymore.

## Commands

Local dev runs in **DDEV**. Prefix PHP/Composer/Node commands with `ddev exec`,
`ddev composer`, `ddev npm` (the host has no PHP 8.3):

```bash
ddev exec php artisan test                       # full suite (also: composer test)
ddev exec php artisan test --filter=RecordResourceTest   # single test class/method
composer lint                  # Pint format-fix  (composer lint:test = check only)
composer analyse               # Larastan / PHPStan (level 6)
ddev npm run build             # build Vite assets (dev: ddev npm run dev)
```

Tests are PHPUnit 12 (not Pest). `composer test` clears the config cache first,
then runs `artisan test`.

Always run `composer lint` and `composer analyse` before considering a change
done — CI ([.github/workflows/ci.yml](.github/workflows/ci.yml)) enforces both
plus the test suite.

## Architecture & conventions

- **Models** (`app/Models`): mass-assignment via the `#[Fillable([...])]`
  attribute (not a `$fillable` array); typed columns via a `casts()` method;
  domain enums in `app/Enums` (`RecordFormat`, `RecordSpeed`, `RecordCondition`,
  `UserRole`). `Record` maps to the `vinyl_records` table; `Track` maps to `tracks`.
- **Entry types**: `Record` (vinyl, public catalog) and `Track` (a standalone,
  admin-only track library — no Record FK, no public surface) are the two content
  entities. `User` and `Setting` are not content. `Track` mirrors `Record`'s
  Filament split-class layout under `app/Filament/Resources/Tracks/`.
- **Public catalog**: `RecordController` (index/show) is **read-only**. All record
  writes go through Filament — never add public write routes. `Record` is the only
  *public* catalog entry type (`Track` is admin-only). Search uses a FULLTEXT
  `MATCH…AGAINST` index (`title/artist/genre/label`) on MariaDB/MySQL, with a
  hand-rolled LIKE fallback for SQLite (tests) and for terms the index can't
  represent (tokens < 3 chars, stopwords). All query input is whitelist-validated
  (enum `tryFrom`, keyed sort map) — keep this public endpoint injection-safe.
- **Settings**: `Setting` is a typed key-value store; read via
  `Setting::getValue($key, $default)` (coerced by `setting_type`), edited through the
  Filament `SettingResource`. `SocialLinksService` reads social URLs from it. A
  separate `config/krate.php` namespace holds app config (`krate.admin_notification_emails`,
  `krate.site.name`).
- **Auth events / email**: both surfaces share the `web` guard, so the `Login` event
  fires for members and staff alike. `SendAdminLoginAlert` (auto-discovered listener)
  filters to staff sign-ins and sends the queued `AdminLoginAlert` notification via
  **Postmark** (`symfony/postmark-mailer`); mail failures are caught + `report()`ed so
  auth never breaks. `GET /dashboard` redirects staff to `/admin` and shows members
  their own view.
- **Filament**: resources live under `app/Filament/Resources/<Model>/` (schema,
  table, and pages split into their own classes, per Filament 5 conventions).
- **Tests** (`tests/Feature`, PHPUnit): use `RefreshDatabase` on SQLite
  `:memory:`. Filament pages are exercised with Livewire (`Livewire::test(...)`,
  `fillForm`, `call('create'|'save')`, `callAction(...)`). Mirror the existing
  style in `AdminPanelTest` / `RecordResourceTest`.
- **Static analysis**: PHPStan runs at **level 6** over `app/` (see
  `phpstan.neon`). Prefer fixing types over adding ignores; the one existing
  ignore is documented inline (`phpstan.neon`).
- **Formatting**: Laravel Pint, default `laravel` preset (`pint.json`).

## Conventions for changes

- Match surrounding code style; keep comments at the existing density.
- Keep the two auth surfaces distinct: Breeze for public members, Filament's
  `canAccessPanel()` for staff. Don't let public flows touch `role`.
- PRs are squash-merged; use `Closes #<n>` in the PR body to auto-close issues.
