# Phantom Framework - Comprehensive Documentation

Welcome to the definitive technical manual for Phantom Framework (v1.12.7). This document covers the entire ecosystem, from the core architecture to the latest real-time features.

---

## 📑 Table of Contents

1.  [**Core Architecture**](#architecture)
    *   [IoC Container](#container)
    *   [Service Providers](#providers)
    *   [Application Lifecycle](#lifecycle)
2.  [**Routing & HTTP**](#routing)
    *   [Defining Routes](#routes)
    *   [Route Parameters](#route-params)
    *   [Named Routes & Groups](#route-groups)
    *   [Method Injection](#method-injection)
    *   [Middleware System](#middleware)
    *   [CSRF Protection](#csrf)
3.  [**Controllers & Requests**](#controllers)
    *   [Basic Controllers](#controllers-basic)
    *   [The Request Object](#request-object)
    *   [Input & Files](#input-files)
    *   [Validation](#validation)
4.  [**Views & Template Engine**](#views)
    *   [Blade-like Syntax](#view-syntax)
    *   [Layouts & Inheritance](#view-layouts)
    *   [Stacks & Pushes](#view-stacks)
    *   [Components & Includes](#view-components)
5.  [**Database & ORM**](#orm)
    *   [Configuration](#db-config)
    *   [Query Builder](#query-builder)
    *   [Active Record Models](#models)
    *   [Fluent Collections](#collections)
    *   [Relationships](#relationships)
    *   [Polymorphism](#polymorphism)
    *   [Eager Loading](#eager-loading)
    *   [Accessors, Mutators & Scopes](#orm-features)
    *   [Soft Deletes](#soft-deletes)
    *   [Model Observers](#observers)
6.  [**API Development**](#api)
    *   [JSON Responses](#json-responses)
    *   [API Resources](#api-resources)
7.  [**Security & Session**](#security)
    *   [Authentication](#authentication)
    *   [Authorization (Gates)](#authorization)
    *   [Session & Flash Data](#session)
8.  [**Real-Time & Events**](#real-time)
    *   [Server-Sent Events (SSE)](#sse)
    *   [Event Broadcasting](#broadcasting)
    *   [WebSockets (Pusher/Soketi)](#websockets)
    *   [Frontend Integration](#frontend-realtime)
9.  [**Notifications**](#notifications)
    *   [Creating Notifications](#creating-notifications)
    *   [Delivery Channels](#channels)
10. [**Task Scheduling**](#scheduling)
11. [**Testing Suite**](#testing)
12. [**Phantom CLI**](#cli)
    *   [Tinker (REPL)](#tinker)
    *   [Generators](#generators)

---

<a name="architecture"></a>
## 1. Core Architecture

<a name="container"></a>
### IoC Container
Phantom uses a powerful Inversion of Control (IoC) container to manage class dependencies.
- **Access:** Use the global `app()` helper.
- **Binding:** `app()->bind('interface', 'implementation');`
- **Singletons:** `app()->singleton('service', function() { ... });`

<a name="providers"></a>
### Service Providers (v1.11)
Service Providers are the central place of all Phantom application bootstrapping. Your own application, as well as all of Phantom's core services, are bootstrapped via providers.

**Registering Providers:**
Add your provider class to the `providers` array in `config/app.php`.

**Creating a Provider:**
```php
namespace App\Providers;
use Phantom\Core\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider {
    public function register() {
        // Bind into container
        $this->app->singleton(PaymentGateway::class, function() {
            return new StripeGateway(config('services.stripe'));
        });
    }

    public function boot() {
        // Perform post-registration booting
    }
}
```

<a name="lifecycle"></a>
### Application Lifecycle
1.  **Entry Point:** `public/index.php` loads the autoloader and creates the `Application` instance.
2.  **Bootstrapping:** The Kernel loads environment variables (`.env`) and configuration.
3.  **Providers:** `register()` is called on all providers, then `boot()` is called.
4.  **Routing:** The request is sent to the Router, passed through Middleware, and dispatched to a Controller.
5.  **Response:** The response is sent back to the browser.

---

<a name="routing"></a>
## 2. Routing & HTTP

<a name="routes"></a>
### Defining Routes
Routes are defined in `routes/web.php`.
```php
use Phantom\Core\Router;

$router->get('/', function() { return 'Home'; });
$router->post('/submit', [Controller::class, 'method']);
```

<a name="route-params"></a>
### Route Parameters
Dynamic segments are wrapped in curly braces.
```php
$router->get('/user/{id}', function($request, $id) {
    return "User Profile: " . $id;
});
```

<a name="route-groups"></a>
### Named Routes & Groups
Groups allow you to share attributes like middleware or prefixes.
```php
$router->group(['prefix' => 'admin', 'middleware' => 'auth', 'as' => 'admin.'], function($router) {
    $router->get('/dashboard', fn() => 'Admin')->name('dashboard');
});
// Generates URL: /admin/dashboard
// Route Name: admin.dashboard
```

<a name="method-injection"></a>
### Method Injection (v1.10.1)
Phantom automatically resolves dependencies type-hinted in your controller methods or route closures via Reflection.
```php
public function update(Request $request, UserService $service, $id) {
    // $request and $service are automatically injected
    // $id is resolved from the route parameter
}
```

<a name="middleware"></a>
### Middleware System
Middlewares provide a convenient mechanism for filtering HTTP requests.
- **Global:** Register in `Router::use()`.
- **Route:** Chain `->middleware('auth')`.

**Creating a Middleware:**
Run `php phantom make:middleware CheckAge`.
```php
public function handle($request, $next) {
    if ($request->input('age') < 18) {
        return redirect('home');
    }
    return $next($request);
}
```

<a name="csrf"></a>
### CSRF Protection
Phantom includes `VerifyCsrfToken` middleware by default.
Use `csrf_field()` helper in your forms:
```html
<form method="POST">
    <?= csrf_field() ?>
    ...
</form>
```

---

<a name="controllers"></a>
## 3. Controllers & Requests

<a name="controllers-basic"></a>
### Basic Controllers
Generate with `php phantom make:controller UserController`.
```php
namespace Phantom\Http\Controllers;
use Phantom\Http\Request;

class UserController extends Controller {
    public function show($id) {
        return view('user.profile', ['user' => User::find($id)]);
    }
}
```

<a name="request-object"></a>
### The Request Object
The `Phantom\Http\Request` instance provides an object-oriented way to interact with the current HTTP request.

<a name="input-files"></a>
### Input & Files
```php
$name = $request->input('name'); // $_POST or $_GET
$all = $request->all();
$file = $request->file('photo'); // Returns UploadedFile instance

if ($file && $file->isValid()) {
    $file->moveTo('uploads/photos');
}
```

<a name="validation"></a>
### Validation (v1.9.5)
Validate incoming data directly from the controller.
```php
try {
    $validated = $request->validate([
        'title' => 'required|min:5',
        'email' => 'required|email'
    ]);
} catch (\Exception $e) {
    // Returns 422 automatically in API context or throws exception
}
```

---

<a name="views"></a>
## 4. Views & Template Engine (v1.10)

Phantom features a powerful, compiled template engine inspired by Blade. Views are stored in `resources/views` and cached in `storage/compiled`.

<a name="view-syntax"></a>
### Blade-like Syntax
- **Echo Data:** `{{ $variable }}` (Escaped) or `{!! $html !!}` (Raw).
- **Control Structures:**
  ```php
  @if($user->isAdmin)
      <p>Admin</p>
  @else
      <p>User</p>
  @endif

  @foreach($users as $user)
      <li>{{ $user->name }}</li>
  @endforeach
  ```

<a name="view-layouts"></a>
### Layouts & Inheritance
Define a master layout and extend it in child views.

**Master (`layouts/app.php`):**
```html
<html>
    <body>
        <div class="sidebar">@section('sidebar') Default @show</div>
        <div class="content">@yield('content')</div>
    </body>
</html>
```

**Child (`home.php`):**
```php
@extends('layouts.app')

@section('content')
    <h1>Home Page</h1>
@endsection
```

<a name="view-stacks"></a>
### Stacks & Pushes (v1.12.5)
Push content to specific stacks in the layout, useful for JS/CSS.
- **In Layout:** `@stack('scripts')`
- **In View:** 
  ```php
  @push('scripts')
      <script src="app.js"></script>
  @endpush
  ```

<a name="view-components"></a>
### Components & Includes
Reuse views with `@include('partials.header', ['active' => 'home'])`.

---

<a name="orm"></a>
## 5. Database & ORM

<a name="db-config"></a>
### Configuration
Configure database connections in `config/database.php`. Supports MySQL and SQLite.

<a name="query-builder"></a>
### Query Builder
Fluent interface for creating database queries.
```php
$users = DB::table('users')
            ->where('votes', '>', 100)
            ->orderBy('name')
            ->limit(10)
            ->get();
```
Methods: `insert`, `update`, `delete`, `count`, `paginate`.

<a name="models"></a>
### Active Record Models
Create with `php phantom make:model Post`.
```php
class Post extends Model {
    protected $table = 'posts';
    protected $primaryKey = 'id';
}
```
**CRUD:**
```php
$post = new Post(['title' => 'My Post']);
$post->save();

$post = Post::find(1);
$post->delete();
```

<a name="collections"></a>
### Fluent Collections (v1.9.5)
ORM results are returned as `Phantom\Core\Collection` instances, providing functional methods:
`map`, `filter`, `reduce`, `pluck`, `first`, `last`, `isEmpty`.

<a name="relationships"></a>
### Relationships
- **One to One:** `return $this->hasOne(Phone::class);`
- **One to Many:** `return $this->hasMany(Comment::class);`
- **Belongs To:** `return $this->belongsTo(User::class);`

<a name="polymorphism"></a>
### Polymorphism (v1.9.4)
Allows a model to belong to more than one other model on a single association.
```php
// Comment Model
public function commentable() { return $this->morphTo(); }

// Post Model
public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
```

<a name="eager-loading"></a>
### Eager Loading (v1.9.3)
Prevent N+1 query issues.
```php
$books = Book::with('author')->get();
```

<a name="orm-features"></a>
### Advanced Model Features (v1.13)
- **Mass Assignment:** Define `$fillable = ['field1', 'field2']` to protect against malicious data injection.
- **Static Creation:** `User::create($data)` instantiates, fills and saves a model in one step.
- **Attribute Casting:** Use `$casts = ['is_admin' => 'bool', 'meta' => 'json']` to transform data automatically.
- **Serialization:** Control JSON output with `$hidden`, `$visible`, and `$appends`.
- **Smart Timestamps:** Automatic `created_at` and `updated_at` management (set `$timestamps = false` to disable).
- **Find or Fail:** `User::findOrFail($id)` throws an exception if the record doesn't exist.

<a name="orm-features-legacy"></a>
### Accessors, Mutators & Scopes (v1.11.2)
- **Accessors:** `getFirstNameAttribute($value)` -> Access as `$user->first_name`.
- **Mutators:** `setPasswordAttribute($value)` -> Set as `$user->password = 'secret'`.
- **Scopes:** `scopeActive($query)` -> Call as `User::active()`.

<a name="soft-deletes"></a>
### Soft Deletes (v1.10.2)
Use `Phantom\Traits\SoftDeletes`. Records are not removed from DB, but `deleted_at` is set.
- `User::withTrashed()->get()`
- `User::onlyTrashed()->get()`
- `$user->restore()`

<a name="observers"></a>
### Model Observers (v1.12.4)
Group model events (`creating`, `created`, `updating`, `deleted`, etc.).
Run `php phantom make:observer UserObserver` and register it: `User::observe(UserObserver::class);`.

---

<a name="api"></a>
## 6. API Development

<a name="json-responses"></a>
### JSON Responses
`Response::json(['status' => 'ok'], 200);`

<a name="api-resources"></a>
### API Resources (v1.10.3)
Transform models into standard JSON formats.
Run `php phantom make:resource UserResource`.
```php
public function toArray() {
    return ['id' => $this->id, 'email' => $this->email];
}
```

---

<a name="security"></a>
## 7. Security & Session

<a name="authentication"></a>
### Authentication
- `auth()->attempt(['email' => $e, 'password' => $p])`
- `auth()->user()`
- `auth()->check()`
- `auth()->logout()`

<a name="authorization"></a>
### Authorization (Gates) (v1.11.3)
Define gates in a ServiceProvider:
```php
gate()->define('edit-post', function($user, $post) {
    return $user->id === $post->user_id;
});
```
Check: `gate()->allows('edit-post', $post)` or `@can('edit-post', $post)`.

<a name="session"></a>
### Session & Flash Data
- `session()->put('key', 'value')`
- `session()->flash('status', 'Task Complete')` (Available only for next request).

---

<a name="real-time"></a>
## 8. Real-Time & Events (v1.12)

<a name="sse"></a>
### Server-Sent Events (SSE)
Stream data over HTTP.
```php
return response()->stream(function($stream) {
    $stream->event(['stock' => 100], 'update');
});
```

<a name="broadcasting"></a>
### Event Broadcasting
Broadcast server-side events to WebSocket channels.
1. Implement `Phantom\Events\ShouldBroadcast`.
2. Define `broadcastOn()` (channel name).
3. Dispatch: `event(new OrderShipped($order))`.

<a name="websockets"></a>
### WebSockets (Pusher/Soketi)
Configure drivers in `config/broadcasting.php`. Set `BROADCAST_DRIVER=pusher` in `.env`.

<a name="frontend-realtime"></a>
### Frontend Integration
Use Laravel Echo or native JS to listen to channels defined in `broadcastOn()`.

---

<a name="notifications"></a>
## 9. Notifications (v1.12.6)

Send notifications via Database, Mail, or Broadcast.
Run `php phantom make:notification InvoicePaid`.

**Notifiable Trait:**
```php
class User extends Model { use Notifiable; }
```

**Sending:**
```php
$user->notify(new InvoicePaid($invoice));
```

---

<a name="scheduling"></a>
## 10. Task Scheduling (v1.12.7)

Define cron tasks in `app/Console/Kernel.php`.
```php
return function($schedule) {
    $schedule->command('migrate')->daily();
    $schedule->call(fn() => Log::info('Run'))->everyMinute();
};
```
Server Cron: `* * * * * php /path/phantom schedule:run`.

---

<a name="testing"></a>
## 11. Testing Suite

Phantom integrates with PHPUnit.
**Feature Tests (`tests/Feature`):**
```php
$this->get('/login')->assertStatus(200);
$this->post('/api/user', $data)->assertJson(['created' => true]);
```

---

<a name="cli"></a>
## 12. Phantom CLI

The `phantom` binary is located in the root directory.

<a name="tinker"></a>
### Tinker (REPL)
Interactive shell to test code: `php phantom tinker`.

<a name="generators"></a>
### Generators
- `make:model`
- `make:controller`
- `make:migration`
- `make:seeder`
- `make:middleware`
- `make:resource`
- `make:observer`
- `make:command`
