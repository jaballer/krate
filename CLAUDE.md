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
ddev exec php artisan test     # full suite (also: composer test)
composer lint                  # Pint format-fix  (composer lint:test = check only)
composer analyse               # Larastan / PHPStan (level 6)
ddev npm run build             # build Vite assets (dev: ddev npm run dev)
```

Always run `composer lint` and `composer analyse` before considering a change
done — CI ([.github/workflows/ci.yml](.github/workflows/ci.yml)) enforces both
plus the test suite.

## Architecture & conventions

- **Models** (`app/Models`): mass-assignment via the `#[Fillable([...])]`
  attribute (not a `$fillable` array); typed columns via a `casts()` method;
  domain enums in `app/Enums` (`RecordFormat`, `RecordSpeed`, `RecordCondition`,
  `UserRole`). `Record` maps to the `vinyl_records` table.
- **Public catalog**: `RecordController` (index/show) is **read-only**. All record
  writes go through Filament — never add public write routes.
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
