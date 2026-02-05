# Phantom Framework Documentation (v1.12.x)

Welcome to the official manual for Phantom, the minimalist PHP framework for modern artisans. This guide provides a deep dive into every corner of the framework.

---

## 📑 Table of Contents
1.  [Architecture & Container](#architecture)
2.  [Routing & Controllers](#routing)
3.  [Middleware System](#middleware)
4.  [Request & Validation](#requests)
5.  [Views & Phantom Templates](#views)
6.  [ORM (Database)](#orm)
    *   [Basic Usage](#orm-basic)
    *   [Relationships](#orm-relationships)
    *   [Polymorphism](#orm-polymorphism)
    *   [Soft Deletes](#orm-soft-deletes)
7.  [Collections](#collections)
8.  [API Resources](#api-resources)
9.  [Testing Suite](#testing)
10. [Real-Time Communication](#real-time)
    *   [Server-Sent Events (SSE)](#sse)
    *   [Event Broadcasting](#broadcasting)
11. [Phantom CLI (Binary)](#cli)
    *   [Generator Commands](#cli-generators)
    *   [Tinker (REPL)](#cli-tinker)

---

<a name="architecture"></a>
## 1. Architecture & Container

Phantom is built on a powerful **IoC (Inversion of Control) Container**. This allows for easy service management and automatic dependency injection.

### Accessing the App
You can use the `app()` helper to access any service registered in the container:
```php
$config = app('config');
$db = app('db');
```

### Service Providers
Providers are the heart of the modular architecture. You can register them in `config/app.php`.
```php
namespace App\Providers;

use Phantom\Core\ServiceProvider;

class MyServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton('service', fn() => new MyService());
    }
}
```

---

<a name="routing"></a>
## 2. Routing & Controllers

Routes are defined in `routes/web.php`. Phantom supports automatic **Method Injection**.

### Basic Routing
```php
$router->get('/user/{id}', function(int $id) {
    return "User: " . $id;
});
```

### Controller Example with Injection
```php
class PostController extends Controller {
    public function store(Request $request, PostService $service) {
        $data = $request->validate(['title' => 'required']);
        return $service->create($data);
    }
}
```

---

<a name="middleware"></a>
## 3. Middleware System

Middlewares intercept requests before they reach your controller.

### Global Middlewares
Register them in the Router to run on every request:
```php
$router->use(\Phantom\Http\Middlewares\VerifyCsrfToken::class);
```

---

<a name="requests"></a>
## 4. Request & Validation

The `Request` object provides a clean API to interact with user input.

### Accessing Data
```php
$request->all();           // Array of all inputs
$request->input('name');   // Get specific field
```

### Inline Validation
```php
$data = $request->validate([
    'email' => 'required|email',
    'age' => 'numeric|min:18'
]);
```

---

<a name="views"></a>
## 5. Views & Phantom Templates

Phantom includes a **Compiled Template Engine** (Blade-like) that caches views in `storage/compiled`.

### Syntax
*   **Echo**: `{{ $var }}` (Escaped) or `{!! $var !!}` (Raw).
*   **Directives**: `@if`, `@foreach`, `@include`, `@extends`, `@section`, `@yield`.
*   **Authorization**: `@can('update', $post) ... @endcan`.

---

<a name="orm"></a>
## 6. ORM (Database)

<a name="orm-basic"></a>
### Basic Usage
```php
$users = User::all(); // Returns a Collection
$user = User::find(1);
$user->save();
```

<a name="orm-relationships"></a>
### Relationships
*   **One to Many**: `return $this->hasMany(Comment::class);`
*   **Belongs To**: `return $this->belongsTo(User::class);`

<a name="orm-soft-deletes"></a>
### Soft Deletes
```php
use Phantom\Traits\SoftDeletes;

class Post extends Model {
    use SoftDeletes;
}
```

---

<a name="collections"></a>
## 7. Collections

The `Collection` class provides a wrapper for arrays with functional methods.

```php
$collection = User::all();
$emails = $collection->pluck('email');
```

---

<a name="api-resources"></a>
## 8. API Resources

Transform your models into JSON structures effortlessly.

```php
return UserResource::make($user);
```

---

<a name="testing"></a>
## 9. Testing Suite

Phantom is built for TDD. Use `FeatureTestCase` to test your routes.

```php
$this->get('/api/users')->assertStatus(200)->assertJson(['status' => 'ok']);
```

---

<a name="real-time"></a>
## 10. Real-Time Communication

<a name="sse"></a>
### Server-Sent Events (SSE)
SSE allows you to stream data from the server to the client over HTTP.

```php
// In a Controller
return response()->stream(function($stream) {
    while(true) {
        $stream->event(['time' => date('H:i:s')], 'timer');
        sleep(1);
    }
});
```

<a name="broadcasting"></a>
### Event Broadcasting
Broadcast your events to the client by implementing `ShouldBroadcast`.

```php
class OrderPlaced implements ShouldBroadcast {
    public function broadcastOn() {
        return ['orders'];
    }
    public function broadcastWith() {
        return ['id' => $this->order->id];
    }
}
```

---

<a name="cli"></a>
## 11. Phantom CLI

<a name="cli-generators"></a>
### Generator Commands
*   `make:model`, `make:controller`, `make:migration`, `make:middleware`, `make:resource`, `make:command`.

<a name="cli-tinker"></a>
### Phantom Tinker (REPL)
```bash
php phantom tinker
```
