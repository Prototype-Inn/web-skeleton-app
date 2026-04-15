# Agent Instructions for web-skeleton-app

## Setup
- Install dependencies: `composer install`

## Development Commands
- Run tests: `composer test` (or `vendor/bin/phpunit tests`)
- Start dev server: `composer serve` (serves `public/` on localhost:8080)
- Dump autoloader: `composer dump-autoload`

## Project Structure
- Entrypoint: `public/index.php` (front controller)
- Source: `src/` (PSR-4: `PrototypeIn\\App\\`)
- Tests: `tests/` (PSR-4: `PrototypeIn\\App\\Tests\\`)
- Configuration: `config/` (see `config/di.php` and `config/routes.php`)

## Notes
- Dependencies are locked via `composer.lock`; avoid direct edits to `vendor/`.
- This skeleton follows MVC/ADR/MVVM patterns; see `PLAN.md` for details.