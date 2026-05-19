# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Initial project setup (install deps, generate key, migrate, build frontend)
composer setup

# Start full dev stack (Laravel server + queue + logs + Vite hot reload)
composer dev

# Run all tests
composer test

# Run a specific test file
./vendor/bin/phpunit tests/Unit/Pages/Domain/PageTest.php

# Run tests by group
./vendor/bin/phpunit --group unit

# Static analysis (PHPStan level 7)
./vendor/bin/phpstan analyse

# Code style (Laravel Pint)
./vendor/bin/pint

# Frontend only
npm run dev    # watch mode
npm run build  # production build
```

## Architecture

This is a **Laravel 13** application structured around **Domain-Driven Design (Clean Architecture)**. Business logic lives in `src/` rather than `app/`.

### Domain Structure

Each bounded context under `src/` follows a strict 4-layer pattern:

```
src/
├── Pages/
│   ├── Domain/           # Entities, value objects, enums, repository interfaces
│   ├── Application/      # Use-case services, DTOs, queries
│   ├── Infrastructure/   # Eloquent repository implementations
│   └── Presentation/     # HTTP controllers, form requests
└── Users/
    ├── Domain/
    ├── Application/
    ├── Infrastructure/
    └── Presentation/
```

- **Domain** — pure business logic, no framework dependencies
- **Application** — orchestrates domain objects, defines repository interfaces as contracts
- **Infrastructure** — Eloquent models and repository implementations (wired via service providers in `app/Providers/`)
- **Presentation** — Laravel controllers and form requests; routes are split into `routes/api.php` (Sanctum-protected) and `routes/web.php`

### Key Design Points

- **Authentication**: Laravel Sanctum token auth. Tokens are managed in the Users domain; there is a token refresh mechanism.
- **Pages**: Support multi-language content stored as JSONB (`title`, `content`, `slug`), parent-child hierarchy, and soft deletes.
- **Routing**: API and web controllers are separate classes even for the same resource.
- **Testing**: Unit tests cover domain entities/services/DTOs/value objects in isolation. Feature tests cover HTTP behavior. PHPStan level 7 enforces strict types across `src/`.
- **CI**: GitHub Actions runs PHPUnit against PostgreSQL 15, excluding `infrastructure` and `presentation` test groups (those run locally only).
- **Local DB**: SQLite for local dev; PostgreSQL in CI and production (Docker Compose includes a Postgres service).
