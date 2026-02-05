# Phantom Framework Documentation (v1.10.x)

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
10. [Phantom CLI (Binary)](#cli)
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

### Manual Binding
In your service providers or boot logic:
```php
app()->singleton(MyService::class, function() {
    return new MyService('config-value');
});
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

### Controller Routing
You don't need to manually inject the Request; Phantom does it for you:
```php
$router->post('/posts', [PostController::class, 'store']);
```

### Controller Example with Injection
```php
namespace Phantom\Http\Controllers;

use Phantom\Http\Request;
use App\Services\PostService;

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

### Route Middlewares
```php
$router->get('/admin', function() { ... })->middleware('auth');
```

---

<a name="requests"></a>
## 4. Request & Validation

The `Request` object provides a clean API to interact with user input.

### Accessing Data
```php
$request->all();           // Array of all inputs
$request->input('name');   // Get specific field (default: null)
$request->file('avatar');  // Get uploaded file
```

### Inline Validation
The `validate` method returns only the validated data or throws a 422 error:
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
*   **Directives**: `@if($cond)`, `@else`, `@endif`, `@foreach($items as $item)`.
*   **Inclusion**: `@include('partials.nav')`.

### Layouts & Inheritance
**layouts/master.php**
```html
<html>
    <body>
        <nav>...</nav>
        @yield('content')
    </body>
</html>
```

**home.php**
```php
@extends('layouts.master')

@section('content')
    <h1>Welcome to Phantom</h1>
@endsection
```

---

<a name="orm"></a>
## 6. ORM (Database)

The Phantom ORM is a fluent Active Record implementation.

<a name="orm-basic"></a>
### Basic Usage
```php
$users = User::all(); // Returns a Collection
$user = User::find(1);
$user->name = 'New Name';
$user->save();
```

### Eager Loading
Avoid the N+1 problem by pre-loading relationships:
```php
$posts = Post::with('user', 'comments')->get();
```

<a name="orm-relationships"></a>
### Relationships
*   **One to One**: `return $this->hasOne(Profile::class);`
*   **One to Many**: `return $this->hasMany(Comment::class);`
*   **Belongs To**: `return $this->belongsTo(User::class);`

<a name="orm-polymorphism"></a>
### Polymorphism
Perfect for comments or images shared across models:
```php
// In Comment model
public function commentable() {
    return $this->morphTo();
}

// In Post model
public function comments() {
    return $this->morphMany(Comment::class, 'commentable');
}
```

<a name="orm-soft-deletes"></a>
### Soft Deletes
Import the trait into your model:
```php
use Phantom\Traits\SoftDeletes;

class Post extends Model {
    use SoftDeletes;
}

$post->delete(); // Sets deleted_at
Post::withTrashed()->get(); // Include deleted
Post::onlyTrashed()->get(); // Only deleted
```

---

<a name="collections"></a>
## 7. Collections

The `Collection` class provides a wrapper for arrays with functional methods.

```php
$collection = User::all();

$emails = $collection->filter(fn($u) => $u->active)
                     ->map(fn($u) => $u->email)
                     ->pluck('email');
```

---

<a name="api-resources"></a>
## 8. API Resources

API Resources allow you to transform your models into JSON structures effortlessly.

```php
class UserResource extends JsonResource {
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => strtoupper($this->name),
            'joined' => $this->created_at
        ];
    }
}

// In controller
return UserResource::make($user);
// Or for lists
return UserResource::collection(User::all());
```

---

<a name="testing"></a>
## 9. Testing Suite

Phantom is built for TDD. Use `FeatureTestCase` to test your routes.

```php
namespace Tests\Feature;

use Tests\FeatureTestCase;

class UserTest extends FeatureTestCase {
    public function test_api_returns_users() {
        $this->get('/api/users')
             ->assertStatus(200)
             ->assertJson(['status' => 'success']);
    }
}
```

---

<a name="cli"></a>
## 10. Phantom CLI

The `phantom` binary is your command center.

<a name="cli-generators"></a>
### Generator Commands
*   `make:model`, `make:controller`, `make:migration`, `make:middleware`, `make:resource`, `make:view`, `make:seeder`.

### Database Management
*   `migrate`: Runs pending migrations and tracks them.
*   `migrate:rollback`: Rolls back the last **batch** of migrations.

<a name="cli-tinker"></a>
### Phantom Tinker (REPL)
Interact with your app in real-time:
```bash
php phantom tinker
phantom> $user = User::find(1);
phantom> $user->name;
=> 'Mario'
```