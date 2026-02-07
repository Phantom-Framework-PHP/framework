# Phantom Framework v1.15.3

Phantom is a minimalist, elegant, and fast PHP framework, designed for developers seeking Laravel's structure with the lightness of a micro-framework.

## 🚀 Quick Installation

You can create a new Phantom project instantly using Composer:

```bash
composer create-project phantom-php/framework my-app
```

Or manually:

1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure your credentials.
4. Start your server: `php phantom serve`.

## 📖 Comprehensive Documentation

Our [Documentation Manual](DOCUMENTATION.md) covers everything you need to master Phantom:

- [🏗️ Core Architecture](DOCUMENTATION.md#architecture)
- [🛣️ Advanced Routing & Method Injection](DOCUMENTATION.md#routing)
- [🛡️ Middlewares & Security](DOCUMENTATION.md#middleware)
- [✅ Validation & Requests](DOCUMENTATION.md#requests)
- [🎨 Template Engine (Blade-like)](DOCUMENTATION.md#views)
- [🗄️ ORM Relationships & Polymorphism](DOCUMENTATION.md#orm)
- [🗑️ Soft Deletes System](DOCUMENTATION.md#orm-soft-deletes)
- [📦 Fluent Collections](DOCUMENTATION.md#collections)
- [🌐 API Resources (JSON)](DOCUMENTATION.md#api-resources)
- [🧪 HTTP Feature Testing](DOCUMENTATION.md#testing)
- [💻 Phantom CLI & Tinker (REPL)](DOCUMENTATION.md#cli)

## Main Features

- **📦 IoC Container**: Professional and powerful dependency management.
- **🛣️ Advanced Routing**: Route Groups, Named Routes, Middlewares, and **Method Injection**.
- **🎨 View Engine**: Blade-like template system with layouts, components, and caching (v1.10).
- **🗄️ Phantom ORM**: Active Record with Eager Loading, Relationships, Polymorphism, Soft Deletes, **Attribute Casting, and Mass Assignment Protection (v1.13)**.
- **🛡️ Native Security**: CSRF protection, data validation, and secure hashing (Argon2/Bcrypt).
- **🌐 Internationalization**: Built-in multi-language support (i18n).
- **✉️ Mail System**: Integrated and easy-to-use email sending system.
- **📁 File Storage**: File management with advanced security validation (MIME + Magic Numbers).
- **🌱 Seeders & Factories**: System for populating the database with test data.
- **📝 Advanced Logging**: Automatic error recording in local logs.
- **🎨 Elegant Error Handling**: Custom Tailwind CSS error views and refined debug mode.
- **🧪 Testing Suite**: Built-in system for HTTP Feature Testing and unit tests.
- **💻 Phantom CLI**: Professional toolkit including `serve`, `migrate`, and `tinker` REPL.
- **📦 Modern Tools**: Native Fluent Collections, API Resources, and Request Validation.
- **⏱️ Smart Timestamps**: Automatic `created_at` and `updated_at` management for models.

## Requirements

- PHP 8.1 or superior.
- Extensions: PDO, OpenSSL, Mbstring, Fileinfo, Readline (recommended).

## Phantom CLI Quick Guide

```bash
# Start Server
php phantom serve

# Interactive REPL
php phantom tinker

# Run Migrations
php phantom migrate

# Generate Code
php phantom make:model Post
php phantom make:resource UserResource
```

## License

This project is under the [MIT License](LICENSE).

---
Designed with ❤️ for speed and elegance.
