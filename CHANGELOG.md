# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.8.1] - 2026-01-28
### Added
- **Full English Localization**: All UI views and documentation (README, Roadmap, Changelog) translated to English.
- **Framework Version Sync**: Global version updated to v1.8.1 across all core files.

## [1.8.0] - 2026-01-28
### Added
- **Advanced Error Handling System**:
  - Custom Tailwind CSS error views for 404 and 500 errors.
  - Refined **Debug Mode**: Professional debug view with interactive stack trace and exception details.
  - Global error capture including syntax errors in route files.
- **Logging System**: Automatic error recording in `storage/logs/phantom.log`.
- **Improved Env Handling**: Correct parsing of boolean (`true`/`false`), `null`, and quoted strings in `.env` files.
- **Apache Support**: Default `.htaccess` file for clean URLs and redirecting to `public/index.php`.

## [1.7.1] - 2026-01-28
### Fixed
- **CLI Syntax**: Fixed backslash escaping issue in the `db:seed` command within the `phantom` binary.

## [1.7.0] - 2026-01-27
### Added
- **Phantom CLI Toolkit**: Code generation suite.
  - Commands `make:migration`, `make:model`, `make:controller`, and `make:view`.
  - Stub system for file customization.
- **Real Migrations**: `migrate` and `migrate:rollback` commands with database logic.
- **Integrated File Security**: `UploadedFile` class with automatic MIME Type and Magic Number validation.

## [1.6.0] - 2026-01-27
### Added
- **Seeder System**: Base class `Phantom\Database\Seeders\Seeder`.
- **CLI Commands**: `make:seeder` and `db:seed`.
- Organized directories: `database/seeders` and `database/factories`.

## [1.5.0] - 2026-01-27
### Added
- `MailManager`, `SmtpTransport`, and `Message` classes for email handling.
- Configuration file `config/mail.php`.
- Global helper `mail_send()`.

## [1.4.0] - 2026-01-27
### Added
- `StorageManager` and `LocalDisk` driver.
- `FileValidator` utility for binary signature validation.
- Configuration file `config/filesystems.php`.
- Global helpers `storage()` and `validate_file()`.

## [1.3.0] - 2026-01-27
### Added
- `QueueManager` and `SyncQueue` driver.
- Configuration file `config/queue.php`.
- Global helper `dispatch()`.

## [1.2.0] - 2026-01-27
### Added
- `Dispatcher` class for event-driven logic.
- Global helper `event()`.
- Support for Closure and Class-based listeners.

## [1.1.0] - 2026-01-27
### Added
- `CacheManager` and `FileStore` driver.
- Configuration file `config/cache.php`.
- Global helpers `cache()` and `storage_path()`.

## [1.0.0] - 2026-01-27
### Added
- **First official stable release.**
- Complete README.md and production-ready entry point.

## [0.9.0] - 2026-01-27
### Added
- CSRF protection with `Csrf` class and `VerifyCsrfToken` middleware.
- Centralized exception handling with `Handler` class.
- Global helpers `csrf_token()` and `csrf_field()`.

## [0.8.0] - 2026-01-27
### Added
- `phantom` console binary.
- Initial commands: `list`, `version`, `migrate`.
- PHPUnit configuration and test directory structure.

## [0.7.0] - 2026-01-27
### Added
- `Translator` class for i18n support.
- Initial language support for English (`en`) and Spanish (`es`).
- Global helpers `__()`, `url()`, and `redirect()`.

## [0.6.0] - 2026-01-27
### Added
- `Pipeline` pattern implementation.
- Middleware support in Router.
- `Validator` engine with built-in rules.

## [0.5.0] - 2026-01-27
### Added
- `Session` management and `AuthManager`.
- Secure password hashing with `Hash` class.
- Config files `config/auth.php` and `config/session.php`.
- User model for authentication.

## [0.4.0] - 2026-01-27
### Added
- Fluent `Query\Builder`.
- `Model` class (Active Record ORM).
- `Schema` and `Blueprint` for database migrations.

## [0.3.0] - 2026-01-27
### Added
- `Database` wrapper for PDO.
- Native PHP `View` engine with buffer capture.
- Singleton registration for DB service.

## [0.2.0] - 2026-01-27
### Added
- `Request` and `Response` abstractions.
- Centralized routing in `routes/web.php`.
- Base `Controller` class.

## [0.1.0] - 2026-01-27
### Added
- Initial directory structure and PSR-4 autoloading.
- IoC `Container`, `Env`, `Config`, and `Application` core classes.
- Basic global helpers and entry point.