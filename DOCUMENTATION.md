# Phantom Framework - Comprehensive Documentation

Welcome to the definitive technical manual for Phantom Framework (v1.15.3). This document covers the entire ecosystem, from the core architecture to the latest performance features.

---

## 📑 Table of Contents

1.  [**Core Architecture**](#architecture)
2.  [**Routing & HTTP**](#routing)
3.  [**Controllers & Requests**](#controllers)
4.  [**Views & Template Engine**](#views)
5.  [**Database & ORM**](#orm)
6.  [**API Development**](#api)
7.  [**Security & Session**](#security)
8.  [**Real-Time & Events**](#real-time)
9.  [**Notifications**](#notifications)
10. [**Internationalization**](#i18n)
11. [**File Storage**](#storage)
12. [**Queue System**](#queues)
13. [**Asset Management (Vite)**](#assets)
14. [**Asynchronous Programming (Fibers)**](#async)
15. [**Testing Suite**](#testing)
16. [**Phantom CLI**](#cli)

---

<a name="architecture"></a>
## 1. Core Architecture

### IoC Container
Phantom uses a powerful Inversion of Control (IoC) container to manage class dependencies.
- **Access:** Use the global `app()` helper.
- **Binding:** `app()->bind('interface', 'implementation');`
- **Singletons:** `app()->singleton('service', function() { ... });`

### Service Providers (v1.11)
Service Providers are the central place of all Phantom application bootstrapping.

---

<a name="routing"></a>
## 2. Routing & HTTP

### Defining Routes
Routes are defined in `routes/web.php`.
```php
use Phantom\Core\Router;

$router->get('/', function() { return 'Home'; });
$router->post('/submit', [Controller::class, 'method']);
```

### Route Parameters
Dynamic segments are wrapped in curly braces: `/user/{id}`.

### Named Routes & Groups
Groups allow you to share attributes like middleware or prefixes.
```php
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', fn() => 'Admin')->name('admin.dashboard');
});
```

---

<a name="controllers"></a>
## 3. Controllers & Requests

### Basic Controllers
Generate with `php phantom make:controller UserController`.

### Validation (v1.9.5)
```php
$validated = $request->validate([
    'title' => 'required|min:5',
    'email' => 'required|email'
]);
```

---

<a name="views"></a>
## 4. Views & Template Engine (v1.10)

Phantom features a powerful, compiled template engine inspired by Blade.
- **Echo Data:** `{{ $variable }}` (Escaped) or `{!! $html !!}` (Raw).
- **Control Structures:** `@if`, `@foreach`, `@extends`, `@section`, `@yield`.

---

<a name="orm"></a>
## 5. Database & ORM

### Fast Read-only Mode (v1.15.1)
For maximum performance in read-only operations, use `toPlainArray()`. This avoids Model hydration.
```php
$rawUsers = User::where('active', 1)->toPlainArray(); // Collection of stdClass
$arrayUsers = User::all()->toPlainArray(true); // Collection of associative arrays
```

### Deferred Hydration (v1.15.2)
Phantom uses lazy attribute casting. Attributes are only processed when accessed, improving performance when retrieving many records.

---

<a name="i18n"></a>
## 10. Internationalization (v1.14.9)

### JSON Translations
Stored in `lang/{locale}.json`.
Access: `__('Welcome')`

### Dynamic Locale Switching
```php
set_locale('es');
```

---

<a name="storage"></a>
## 11. File Storage (v1.14)

### Drivers
- **Local:** Local server storage.
- **FTP:** Remote FTP storage.
- **S3:** AWS S3 and compatible services.

---

<a name="queues"></a>
## 12. Queue System (v1.14)

### Drivers
- **Sync:** Immediate execution.
- **Database:** Persistent storage in DB.
- **Redis:** High-performance caching queue.

### Worker
Run `php phantom queue:work` to start processing jobs.

---

<a name="assets"></a>
## 13. Asset Management (Vite) (v1.14.8)

Use the `vite()` helper in your layouts:
```html
<?= vite(['resources/js/app.js', 'resources/css/app.css']) ?>
```

---

<a name="async"></a>
## 14. Asynchronous Programming (Fibers) (v1.15.3)

Phantom provides native wrappers for PHP **Fibers**, allowing for cooperative multitasking.

### Basic Usage
```php
$result = async(function() {
    return "Finished";
});
```

---

<a name="testing"></a>
## 15. Testing Suite

Phantom integrates with PHPUnit. Use `FeatureTestCase` for integration tests.

---

<a name="cli"></a>
## 16. Phantom CLI

The `phantom` binary provides commands for generation and management.
- `php phantom serve`
- `php phantom tinker`
- `php phantom migrate`
- `php phantom queue:work`