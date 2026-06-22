# Changelog

All notable changes to Krate are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project aims to
follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **Rewrote Krate on Laravel 13 + Filament 5**, replacing the original hand-rolled
  PHP MVC app. The migration was tracked as an epic and landed as a series of
  sub-tasks: project scaffold, database schema, Eloquent models, public member
  auth (Breeze), Filament staff admin panel, the read-only public catalog, the
  service layer + uploads (Storage), and the Tailwind CSS v4 frontend (replacing
  Bootstrap).

### Added

- Automated quality safety net: feature tests (PHPUnit), Larastan (PHPStan,
  level 6), Laravel Pint, and a GitHub Actions CI pipeline.

### Removed

- The entire legacy codebase (`legacy/`): the old `src/` MVC layer, `public/`
  page scripts, Twig templates, `config/bootstrap.php`, and the Gulp asset
  pipeline.
