# Phantom Framework Documentation

Welcome to the official Phantom documentation. This guide will help you master the framework from basic routing to advanced ORM relationships.

---

## Table of Contents
1. [Routing](#routing)
2. [Controllers & Requests](#controllers)
3. [Views & Template Engine](#views)
4. [Database & ORM](#orm)
5. [CLI Toolkit (Phantom binary)](#cli)
6. [Collections](#collections)
7. [Validation](#validation)

---

<a name="routing"></a>
## 1. Routing

Routes are defined in `routes/web.php`. Phantom supports GET and POST methods, route groups, and named routes.

### Basic Routes
```php
$router->get('/welcome', function() {
    return 'Hello World!';
});
```

### Dynamic Parameters
```php
$router->get('/user/{id}', function($request) {
    return "User ID: " . $request->input('id');
});
```

### Route Groups
```php
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', [DashboardController::class, 'index'])->name('admin.dash');
});
```

---

<a name="controllers"></a>
## 2. Controllers & Requests

### Controller Example
```php
namespace Phantom\Http\Controllers;

use Phantom\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = User::find($request->input('id'));
        return view('profile', compact('user'));
    }
}
```

### Handling Requests
```php
$request->all();                 // Get all inputs
$request->input('key');          // Get specific input (GET, POST or Route param)
$request->validate(['name' => 'required|min:3']); // Validate data
```

---

<a name="views"></a>
## 3. Views & Template Engine (v1.10)

Phantom uses a Blade-inspired engine. Files should be saved as `.php` in `resources/views`.

### Layouts (@extends, @yield, @section)
**resources/views/layouts/app.php**
```html
<html>
    <body>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

**resources/views/home.php**
```php
@extends('layouts.app')

@section('content')
    <h1>Welcome {{ $name }}</h1>
    @foreach($items as $item)
        <p>{{ $item }}</p>
    @endforeach
@endsection
```

---

<a name="orm"></a>
## 4. Database & ORM

### Basic Queries
```php
$users = User::all();
$user = User::where('email', 'admin@example.com')->first();
```

### Relationships
```php
// Define in Model
public function posts() {
    return $this->hasMany(Post::class);
}

// Use with Eager Loading (Prevent N+1)
$users = User::with('posts')->get();
```

### Polymorphic Relationships
```php
// In Comment Model
public function commentable() {
    return $this->morphTo();
}

// Usage
$comment = Comment::find(1);
$parent = $comment->commentable; // Can be a Post, Video, etc.
```

---

<a name="cli"></a>
## 5. CLI Toolkit (Phantom)

Use the `phantom` binary to speed up development.

| Command | Description |
|---------|-------------|
| `php phantom list` | List all available commands |
| `php phantom make:model User` | Create a new Model |
| `php phantom make:controller HomeController` | Create a new Controller |
| `php phantom make:migration create_posts_table` | Create a migration |
| `php phantom migrate` | Run all pending migrations |
| `php phantom migrate:rollback` | Rollback the last batch of migrations |
| `php phantom make:middleware AuthMiddleware` | Create a new Middleware |

---

<a name="collections"></a>
## 6. Collections

Collections provide a fluent interface for working with arrays of data.

```php
$collection = collect([1, 2, 3, 4, 5]);

$filtered = $collection->filter(fn($n) => $n > 2)
                       ->map(fn($n) => $n * 10);

$emails = $users->pluck('email');
```

---

<a name="validation"></a>
## 7. Validation

Available rules: `required`, `email`, `numeric`, `min:value`, `max:value`.

```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8'
]);
```
