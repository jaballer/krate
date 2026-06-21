# Krate

Krate is a PHP web app for cataloguing a vinyl record collection. It centers on records, with user accounts and a small admin area. The app uses Composer for PHP dependencies, Twig for templating, MySQL/MariaDB for storage, and a Gulp pipeline for frontend assets.

This repository is currently a hybrid codebase:

- The main entrypoint is `public/index.php`.
- Core services and controllers live under `src/`.
- Some pages are routed through `src/Routes/web.php`.
- Many user/record flows still exist as direct legacy scripts under `public/`.

## What It Includes

- Record listing/search and record CRUD pages
- User login and profile/admin-related pages
- Admin dashboard/settings flow
- Twig-based page rendering alongside PHP view templates
- Sass, JS bundling, BrowserSync, and image optimization via Gulp

## Tech Stack

- PHP 8.1
- MariaDB/MySQL
- Twig 3
- Dotenv
- Bootstrap 4.6
- jQuery 3.7
- Gulp
- DDEV for local development

## Local Development

The practical local setup for this repo is DDEV. The checked-in DDEV config uses:

- PHP 8.1
- MariaDB 10.11
- `public/` as the docroot
- project URL `https://krate.ddev.site`

### Prerequisites

- DDEV
- Docker/OrbStack
- Node.js/npm
- Composer

### Start The Project

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Start DDEV:

```bash
ddev start
```

4. Import the sample database dump if you need local data:

```bash
ddev import-db --src=db.sql
```

5. Open the site:

```bash
ddev launch
```

Or visit `https://krate.ddev.site`.

## Environment Variables

The app reads configuration from a root `.env` file via `vlucas/phpdotenv`.

At minimum, the code expects these keys:

- `DB_SERVER`
- `DB_USER`
- `DB_PASS`
- `DB_NAME`
- `SITE_OWNER`
- `SITE_AUTHOR`
- `SITE_NAME`
- `SITE_TAGLINE`
- `SITE_DESCRIPTION`

Optional but used by some flows:

- `APP_ENV`
- `POSTMARK_API_TOKEN` — Postmark server token; login notifications are skipped if unset.
- `MAIL_FROM_ADDRESS` — "from" address for notification emails (falls back to `no-reply@localhost`).
- `ADMIN_NOTIFICATION_EMAILS` — comma-separated recipients for admin/login notifications (falls back to `admin@localhost`).

See `.env.example` for the full list with placeholder values.

## Frontend Assets

The asset pipeline is driven by `gulpfile.js`.

Useful commands:

```bash
npx gulp
```

Runs the default task: cleans generated CSS, rebuilds styles/scripts, and starts BrowserSync against `https://krate.ddev.site/`.

```bash
npx gulp style
```

Builds Sass into `public/assets/css`.

```bash
npx gulp scripts
```

Lints and rebuilds frontend JS bundles.

```bash
npx gulp imageminify
```

Optimizes images in `public/assets/images`.

## Database

This project does not use framework migrations or `php artisan`.

- The checked-in schema/data snapshot is `db.sql`.
- To export the current DDEV database:

```bash
ddev exec mysqldump -u root -proot db > db.sql
```

> **Note:** `db.sql` contains anonymized sample data only — no real user data. Every seeded account (e.g. the `admin` user) uses the password **`Password123!`**. Change these credentials before using the dump for anything beyond local development.

## Project Structure

```text
config/          bootstrap and app wiring
public/          webroot and legacy PHP endpoints
src/Controllers  controllers
src/Core         router, helpers, validation, database classes
src/Models       domain models
src/Routes       route definitions
src/Services     business logic/services
src/Views        PHP and Twig views/templates
public/assets/   Sass, JS, generated CSS, images
```

## Current State

This is not a full framework app and the routing migration is not complete yet.

- `public/index.php` bootstraps the app and hands requests to the custom router.
- Only a subset of routes are currently defined in `src/Routes/web.php`.
- Several features still rely on direct script endpoints such as `public/users/login.php` and `public/records/index.php`.

That means local development and debugging should assume a mixed routed/legacy application, not a finished centralized MVC/router setup.
