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
    *   [Model Observers](#orm-observers)
7.  [Collections](#collections)
8.  [API Resources](#api-resources)
9.  [Testing Suite](#testing)
10. [Real-Time Communication](#real-time)
    *   [Server-Sent Events (SSE)](#sse)
    *   [Event Broadcasting](#broadcasting)
    *   [Frontend Real-time (Echo)](#frontend-realtime)
11. [Phantom CLI (Binary)](#cli)
    *   [Generator Commands](#cli-generators)
    *   [Tinker (REPL)](#cli-tinker)

---

<a name="architecture"></a>
## 1. Architecture & Container

Phantom is built on a powerful **IoC (Inversion of Control) Container**.

### Service Providers
Providers are the heart of the modular architecture. Register them in `config/app.php`.
```php
class MyServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton('service', fn() => new MyService());
    }
}
```

---

<a name="routing"></a>
## 2. Routing & Controllers

Phantom supports automatic **Method Injection**.

### Basic Routing
```php
$router->get('/user/{id}', function(int $id) {
    return "User: " . $id;
});
```

---

<a name="views"></a>
## 5. Views & Phantom Templates

Phantom includes a **Compiled Template Engine** (Blade-like).

### Syntax
*   **Echo**: `{{ $var }}` (Escaped) or `{!! $var !!}` (Raw).
*   **Directives**: `@if`, `@foreach`, `@include`, `@extends`, `@section`, `@yield`.
*   **Stacks**: Inyect content from children to layouts using `@push('name')` and `@stack('name')`.
*   **Authorization**: `@can('update', $post) ... @endcan`.

---

<a name="orm"></a>
## 6. ORM (Database)

<a name="orm-observers"></a>
### Model Observers
Observers group model event listeners.
```php
User::observe(UserObserver::class);
```
Available events: `creating`, `created`, `updating`, `updated`, `deleting`, `deleted`.

---

<a name="real-time"></a>
## 10. Real-Time Communication

<a name="sse"></a>
### Server-Sent Events (SSE)
SSE allows you to stream data from the server to the client.
```php
return response()->stream(function($stream) {
    $stream->event(['msg' => 'hello'], 'message');
});
```

<a name="broadcasting"></a>
### Event Broadcasting
Broadcast your events to the client by implementing `ShouldBroadcast`.

```php
class OrderPlaced implements ShouldBroadcast {
    public function broadcastOn() { return ['orders']; }
    public function broadcastWith() { return ['id' => 1]; }
}
```

<a name="frontend-realtime"></a>
### Frontend Real-time (Echo)
Use `@stack('scripts')` in your layout and `@push('scripts')` in your views to initialize client-side WebSocket connections.

---

<a name="cli"></a>
## 11. Phantom CLI

*   `php phantom serve`: Start dev server.
*   `php phantom tinker`: Interactive REPL.
*   `php phantom make:observer`: Create model observer.
