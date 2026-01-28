# **Development Context for PHP Framework: "Phantom Framework"**

## **Project Vision**
Create a minimalist yet powerful PHP framework that combines Laravel's elegance and structure with the lightness and performance of micro-frameworks, surpassing CodeIgniter 4 in modernity and security.

---

## **STRATEGIC PRIORITIES (Ordered)**

### **Level 1: Critical Foundations**
1. **Security by Design**
   - Automatic input sanitization
   - Native CSRF protection
   - XSS and SQL Injection prevention
   - Password hashing with Argon2id
   - Preconfigured HTTP security headers
   - Integrated rate limiting

2. **SOLID Architecture and Optimization**
   - Light IoC Container
   - Automatic Dependency Injection
   - Repository Pattern for models
   - Lazy loading of components
   - Route and config caching

3. **Authentication System (Phantom Auth)**
   - API Tokens similar to Sanctum
   - Web authentication with secure sessions
   - Role/Permission authorization middleware
   - Automatic refresh tokens
   - Optional 2FA

### **Level 2: Essential Components**
4. **Database Manager (Phantom ORM)**
   - Eloquent-like fluent Query Builder
   - Native MySQL/MariaDB support
   - Multiple connections with pooling
   - Integrated migrations
   - Seeders and factories

5. **Routing System**
   - Separate web and API routes
   - Route caching for production
   - Middleware pipeline
   - Route model binding
   - Automatic parameter validation

6. **View System (Phantom Blade Lite)**
   - Compiled template engine
   - Layout inheritance
   - Components and slots
   - Automatic variable escaping
   - Sections and stacks

---

## **TECHNICAL SPECIFICATIONS**

### **Minimum Requirements**
- PHP 8.1+
- Composer
- Extensions: PDO, JSON, MBstring, OpenSSL

### **Target Benchmarks**
- Load time: < 50ms
- Base memory: < 10MB
- Requests/sec: > 1000 (on modest hardware)

---

## **DEVELOPMENT RULES**
1. **Changelog Integrity**: NEVER delete or truncate history in `CHANGELOG.md`. Always append new versions at the top and maintain the complete history of previous versions.
2. **Backward Compatibility**: Ensure that new features do not break existing core functionality unless a MAJOR version bump is planned.
3. **Documentation Sync**: Any change in core functionality must be reflected in `README.md` and `ROADMAP.md`.

**Final Note**: The framework must remain minimalist by default...
