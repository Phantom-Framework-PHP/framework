# Phantom Framework v1.5.0

Phantom es un framework PHP minimalista, elegante y rápido, diseñado para desarrolladores que buscan la estructura de Laravel con la ligereza de un micro-framework.

## Características Principales

- **📦 Contenedor IoC**: Gestión de dependencias potente y sencilla.
- **🛣️ Enrutamiento Fluido**: Soporte para rutas web, middlewares y controladores.
- **🛡️ Seguridad Nativa**: Protección CSRF, validación de datos y hashing con Argon2/Bcrypt.
- **🗄️ Phantom ORM**: Query Builder fluido y modelo Active Record.
- **🎨 Motor de Vistas**: Sistema de plantillas PHP nativo limpio y eficiente.
- **🌐 Internacionalización**: Soporte multi-idioma integrado.
- **💻 Phantom CLI**: Interfaz de línea de comandos para automatización.
- **✉️ Mail System**: Sistema de envío de correos electrónico integrado.
- **📁 File Storage**: Gestión de archivos con validación de seguridad avanzada.

## Requisitos

- PHP 8.1 o superior.
- Extensiones: PDO, OpenSSL, Mbstring, Fileinfo.

## Instalación rápida

1. Clona el repositorio.
2. Ejecuta `composer install`.
3. Copia `.env.example` a `.env` y configura tus credenciales.
4. Inicia tu servidor: `php -S localhost:8000 -t public`.

## Uso básico

### Definir una ruta
```php
// routes/web.php
$router->get('/hola', function() {
    return view('welcome', ['name' => 'Usuario']);
});
```

### Usar el ORM
```php
$users = User::where('active', 1)->get();
```

## Phantom CLI

El framework incluye una potente interfaz de línea de comandos para automatizar tareas. Puedes ejecutarla usando el binario `phantom` en la raíz del proyecto:

```bash
# Listar todos los comandos disponibles
php phantom list

# Ver la versión actual del framework
php phantom version

# Ejecutar las migraciones de la base de datos
php phantom migrate
```

## Contribuir

¡Las contribuciones son bienvenidas! Por favor, revisa las guías de contribución antes de enviar un Pull Request.

## Licencia

Este proyecto está bajo la [Licencia MIT](LICENSE).

---
Diseñado con ❤️ para la velocidad y la elegancia.