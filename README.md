# Phantom Framework v1.19.5

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
- **🎨 View Engine**: Blade-like template system with layouts, components, and caching.
- **🗄️ Phantom ORM**: Active Record with Eager Loading, Relationships, Polymorphism, Soft Deletes, **Attribute Casting, and Mass Assignment Protection**.
- **🏢 Multi-Tenancy Core (v1.19.3)**: Native support for database and scope isolation (shared or separate DBs).
- **⏱️ Rate Limiting Pro (v1.19.4)**: Distributed sliding window algorithm for precise request throttling.
- **⚡ Distributed Cache (v1.19.5)**: High-performance caching with Redis Cluster support and automatic tenant isolation.
- **🤖 AI Native Integration**: Built-in support for Gemini/OpenAI, Vector Embeddings, and AI-powered validation.
- **📟 Phantom Live**: Build reactive, dynamic interfaces with PHP and Blade (Livewire-style).
- **📊 Phantom Pulse**: Real-time telemetry dashboard for requests, DB queries, and security.
- **🛡️ Native Security**: Zero-config Security Shield (IP Reputation), CSRF protection, and secure hashing.
- **📁 File Storage**: Abstracted storage with Local, FTP, and S3 drivers.
- **📦 Distributed Queues**: Background job processing with Redis Cluster and Sentinel support.
- **📝 API Auto-Doc**: Generate OpenAPI/Swagger documentation automatically using AI.
- **🧪 Testing Suite**: Built-in system for HTTP Feature Testing and unit tests.
- **💻 Phantom CLI**: Professional toolkit including `serve`, `migrate`, `tinker`, and `ai:generate`.

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
