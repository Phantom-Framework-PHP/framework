# Phantom Framework v1.9.2

Phantom is a minimalist, elegant, and fast PHP framework, designed for developers seeking Laravel's structure with the lightness of a micro-framework.

## Main Features

- **📦 IoC Container**: Simple and powerful dependency management.
- **🛣️ Advanced Routing**: Route Groups, Named Routes, Middlewares, and Controllers.
- **🛡️ Native Security**: CSRF protection, data validation, and hashing with Argon2/Bcrypt.
- **🗄️ Phantom ORM**: Fluent Query Builder and Active Record model.
- **🎨 View Engine**: Clean and efficient native PHP template system.
- **🌐 Internationalization**: Built-in multi-language support.
- **💻 Phantom CLI**: Command line interface for automation and code generation.
- **✉️ Mail System**: Integrated email sending system.
- **📁 File Storage**: File management with advanced security validation (MIME + Magic Numbers).
- **🌱 Seeders & Factories**: System for populating the database with test data.
- **📝 Advanced Logging**: Automatic error recording in local logs.
- **🎨 Elegant Error Handling**: Custom Tailwind CSS error views and refined debug mode.

## Requirements

- PHP 8.1 or superior.
- Extensions: PDO, OpenSSL, Mbstring, Fileinfo.

## Quick Installation

1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure your credentials.
4. Start your server: `php -S localhost:8000 -t public`.

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

// Generating URLs
$url = route('admin.dashboard');
```

### Using the ORM
```php
$users = User::where('active', 1)->get();
```

## Phantom CLI

The framework includes a powerful command-line interface. You can run it using the `phantom` binary in the project root:

### General Commands
```bash
# List all available commands
php phantom list

# See current framework version
php phantom version
```

### Database Management
```bash
# Run migrations
php phantom migrate

# Rollback last migration
php phantom migrate:rollback

# Seed the database
php phantom db:seed
```

### Code Generation (Scaffolding)
```bash
# Create a new migration
php phantom make:migration create_posts_table

# Create a new model
php phantom make:model Post

# Create a new controller
php phantom make:controller PostController

# Create a new view (supports dot notation)
php phantom make:view posts.index

# Create a new seeder
php phantom make:seeder PostSeeder
```

## License

This project is under the [MIT License](LICENSE).

---
Designed with ❤️ for speed and elegance.