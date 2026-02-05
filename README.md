# Phantom Framework v1.10.5

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
4. Start your server: `php -S localhost:8000 -t public`.

## 📖 Documentation

For a detailed guide on how to use Phantom, check out our [Documentation Manual](DOCUMENTATION.md).

- [Routing & Middlewares](DOCUMENTATION.md#routing)
- [Controllers & Requests](DOCUMENTATION.md#controllers)
- [View Engine (Phantom Templates)](DOCUMENTATION.md#views)
- [Database & ORM](DOCUMENTATION.md#orm)
- [CLI Toolkit](DOCUMENTATION.md#cli)

## Main Features

- **📦 IoC Container**: Simple and powerful dependency management.
- **🛣️ Advanced Routing**: Route Groups, Named Routes, Middlewares, and Controllers.
- **🎨 View Engine**: Blade-like template system with layouts and components (v1.10).
- **🛡️ Native Security**: CSRF protection, data validation, and hashing.
- **🗄️ Phantom ORM**: Active Record with Eager Loading, Relationships, and Polymorphism.
- **📦 Modern Tools**: Integrated Collection engine, Mailer, Cache, and Queue systems.
- **💻 Phantom CLI**: Professional command line interface for automation.
- **📁 File Storage**: File management with advanced security validation.
- **📝 Advanced Logging**: Automatic error recording in local logs.
- **🎨 Elegant Error Handling**: Custom Tailwind CSS error views and refined debug mode.

## Requirements

- PHP 8.1 or superior.
- Extensions: PDO, OpenSSL, Mbstring, Fileinfo.

## Basic Usage

### Defining a Route
```php
// routes/web.php
use Phantom\Core\Router;

// Simple Route
$router->get('/hello', function() {
    return view('welcome', ['name' => 'User']);
})->name('hello');

// Route Group with Prefix and Middleware
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function(Router $router) {
    $router->get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});
```

### Using the ORM
```php
// Eager loading and Collections
$users = User::with('posts')->get();
$emails = $users->pluck('email');
```

## Phantom CLI

The framework includes a powerful command-line interface. You can run it using the `phantom` binary in the project root:

```bash
# List all available commands
php phantom list

# Run migrations (with tracking)
php phantom migrate

# Create Scaffolding
php phantom make:model Post
php phantom make:controller PostController
php phantom make:middleware AuthMiddleware
```

## License

This project is under the [MIT License](LICENSE).

---
Designed with ❤️ for speed and elegance.
