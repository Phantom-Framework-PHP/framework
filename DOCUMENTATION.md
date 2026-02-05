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
11. [Notification System](#notifications)
12. [Task Scheduling](#scheduling)
13. [Phantom CLI (Binary)](#cli)
    *   [Generator Commands](#cli-generators)
    *   [Tinker (REPL)](#cli-tinker)

---

<a name="architecture"></a>
## 1. Architecture & Container

Phantom is built on a powerful **IoC (Inversion of Control) Container**.

---

<a name="orm"></a>
## 6. ORM (Database)

<a name="orm-observers"></a>
### Model Observers
Observers group model event listeners.
```php
User::observe(UserObserver::class);
```

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

<a name="frontend-realtime"></a>
### Frontend Real-time (Echo)
Use `@stack('scripts')` in your layout and `@push('scripts')` in your views to initialize client-side WebSocket connections.

---

<a name="notifications"></a>
## 11. Notification System
Phantom provides a unified way to send notifications via various delivery channels.

### Sending Notifications
```php
$user->notify(new NewOrderNotification($order));
```
Available channels: `database`, `mail`, `broadcast`.

---

<a name="scheduling"></a>
## 12. Task Scheduling
Phantom allows you to define recurring tasks in `app/Console/Kernel.php`.

### Defining Schedules
```php
// app/Console/Kernel.php
return function($schedule) {
    $schedule->command('migrate')->daily();
};
```

### Running the Scheduler
Add a cron entry: `* * * * * php /path/phantom schedule:run`.

---

<a name="cli"></a>
## 13. Phantom CLI

*   `php phantom serve`: Start dev server.
*   `php phantom tinker`: Interactive REPL.
*   `php phantom schedule:run`: Run scheduled tasks.
