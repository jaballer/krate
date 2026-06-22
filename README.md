# Krate

Krate is a vinyl record collection manager. It has two surfaces that share one
`users` table:

- **Public catalog** — a read-only, searchable browse/detail view of the record
  collection (Blade + Tailwind CSS v4 + Alpine.js). Members sign in via Laravel
  Breeze.
- **Staff back-office** — a Filament admin panel for managing records, users, and
  site settings. Access is role-gated: only `Administrator`/`Manager` users pass
  `canAccessPanel()`.

Built on **Laravel 13** (PHP 8.3+) with **Filament 5**. This replaces the original
hand-rolled PHP MVC app (Twig + Gulp + Bootstrap), which has been fully retired.

## Tech stack

| Area            | Choice                                                       |
| --------------- | ------------------------------------------------------------ |
| Framework       | Laravel 13, PHP 8.3+                                          |
| Admin panel     | Filament 5                                                    |
| Auth            | Laravel Breeze (public members), Filament (staff)            |
| Frontend        | Blade, Tailwind CSS v4, Alpine.js, Vite                      |
| Database        | MariaDB (local via DDEV); SQLite in tests                    |
| Local dev       | DDEV (Docker)                                                |
| Quality         | PHPUnit, Larastan (PHPStan), Laravel Pint, GitHub Actions CI |

## Local development

The supported setup is [DDEV](https://ddev.com/). The checked-in config uses PHP
8.3, MariaDB, nginx, and serves at `https://krate.ddev.site`.

```bash
ddev start
ddev composer install
ddev exec cp .env.example .env
ddev exec php artisan key:generate
ddev exec php artisan migrate --seed
ddev npm install
ddev npm run build      # or: ddev npm run dev
```

Then open https://krate.ddev.site. The Filament panel lives at `/admin`.

## Domain model

- **Record** (`vinyl_records` table) — the catalog entry: title, artist, genre,
  format/speed/condition (enums), cover images, BPM, purchase info.
- **User** — shared across both surfaces; `role` (enum) gates Filament access.
- **Setting** — key/value site configuration (social links, etc.) read by the
  public catalog.

## Testing & quality

```bash
ddev exec php artisan test     # or: composer test
composer lint                  # Pint (formatting); composer lint:test to check only
composer analyse               # Larastan / PHPStan (level 6)
```

CI ([.github/workflows/ci.yml](.github/workflows/ci.yml)) runs Pint, Larastan,
asset build, a migration smoke check, and the test suite on every push to `main`
and on pull requests.

## License

MIT.
