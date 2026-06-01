# AGENTS.md

Laravel 10 API — modular monolith, Oracle DB, JWT auth, Redis cache/queue/session.

## Commands

```bash
composer install && pnpm install   # deps (both required)
php artisan key:generate           # APP_KEY
php artisan jwt:secret             # JWT_SECRET
php artisan serve                  # dev server (port 8000)
php artisan test                   # run all (no tests exist yet — tests/ dir not created)
./vendor/bin/pint                  # Laravel Pint for PHP formatting
npx prettier --write .             # Prettier for JS/JSON/md/etc formatting
php -l app/Modules/SomeModule/    # PHP syntax check
docker compose up -d --build      # full stack (multi-stage build: vendor → php-ext → runtime)
docker compose --profile local up -d  # dev with local-only services
```

Format order: `pint -> prettier` (Pint handles PHP; Prettier handles everything else via `@prettier/plugin-php` with `tabWidth: 2`). `.editorconfig` uses `indent_size: 4` for PHP — trust Pint over Prettier for PHP.

## Architecture

### Modular structure

```
app/Modules/{Name}/
  Controllers/     # DI with UseCases, extend App\Http\Controllers\Controller
  Models/          # Eloquent models, Oracle tables with SECURITY schema prefix
  Repositories/    # Interface + Implementation + CacheDecorator
  UseCases/        # single class per business operation
  Requests/        # BaseRequest (abstract, getValidationAction()) + per-op Request
  msg/             # useMsg::get('module.key') — NOT Laravel's trans() (file-based, Spanish)
  Providers/       # binds Interface → CacheDecorator → Repository
  routes.php       # no Route::prefix — routes/modules.php auto-prefixes as api/{mod}
```

### Route auto-discovery

`routes/modules.php` loops `app/Modules/*`, prefixes each module's `routes.php` as `api/{module_lowercase}`. No manual registration needed. `RouteServiceProvider` points to `routes/modules.php`.

### Module registration

Each `Providers/{Name}ServiceProvider` must be added to `config/app.php` `providers` array (alongside `Yajra\Oci8\Oci8ServiceProvider`). The `create_module.php` script scaffolds a full module and auto-registers it.

### Middleware chain (applied in module routes.php)

```
throttle:{limiter} → jwt.cookie → active.session → permission:{module}.{action}
```

| Rate limiter | Limit | Routes |
|---|---|---|
| `throttle:auth` | 5/min by user/IP | login |
| `throttle:auth-refresh` | 10/min by IP | refresh |
| `throttle:api` | 60/min by user/IP | all protected CRUD |
| `throttle:session` | 30/min by user/IP | session check |
| `throttle:audit` | 120/min by user/IP | audit logs |

Defined in `RouteServiceProvider`. Public routes (login, refresh) have `throttle:auth` but no auth middleware.

### Cross-module composition

UseCases commonly call UseCases from other modules (e.g. `AuditLogger::logAuthentication()` from Audit). Inject in constructor, not `app()` helper.

## Database

- Oracle via `yajra/laravel-oci8`, connection: `DB::connection('oracle')`
- Schema prefix on table names: `SECURITY.USUARIOS`, `SECURITY.ROLES`, etc.
- Models: `$incrementing = false`, `$keyType = 'string'`, `$primaryKey` set explicitly
- Redis used for cache (db0), session (db2), queue (db3) — `predis` client

## Commit conventions

Reglas fundamentales:
1. **Un commit = un solo módulo o un único cambio lógico** — no mezclar features, refactors o fixes de distintas partes.
2. **Formato**: `tipo(alcance): descripción` en español, siguiendo conventional commits.

### Tipos

| Tipo | Uso |
|---|---|
| `feat` | Nueva funcionalidad o módulo nuevo |
| `refactor` | Cambios estructurales, renombres, mejora sin alterar funcionalidad |
| `fix` | Corrección de errores |
| `docs` | Cambios en documentación |
| `style` | Formato, espacios, punto y comas, etc. |
| `test` | Añadir o modificar pruebas |
| `chore` | Build, dependencias, configuración |
| `db` | Migraciones, seeds, cambios en base de datos |

### Ejemplos (basados en el proyecto)

```
feat(payment): Implementar el caso de uso «Update Payment Method» y los DTO relacionados
refactor: Actualizar AGENTS.md para reflejar el nombre correcto del proyecto
feat: agregar scripts de seed para roles
refactor: Eliminar DTOs no utilizados para auditoría
chore(docker): Cambiar Oracle a PostgreSQL local y actualizar docker-compose
db: Agregar migración de usuarios y roles
```

### Flujo

1. Verificar `git status --short` y `git diff --staged --name-only`.
2. Si no hay staged, revisar cambios no staged y decidir qué agregar.
3. Detectar mezcla de módulos/tipos — si hay más de uno, preguntar si dividir.
4. Generar mensaje sugerido y preguntar antes de commitear.

## Notable conventions

- **No tests exist** — `tests/` directory is absent. `phpunit.xml` is configured (Unit + Feature suites).
- **CI** — `.github/workflows/ci.yml` runs lint (Pint, Prettier, PHP syntax check) and test jobs on push/PR. Test job starts a Redis service and uses SQLite in-memory.
- **No BaseController** — controllers extend `App\Http\Controllers\Controller` directly.
- **Pre-commit hook**: `.husky/pre-commit` runs `npx lint-staged` (formats staged PHP/JS/JSON/md files via Prettier).
- **Keys in `.env`**: `JWT_SECRET`, `JWT_REFRESH_SECRET`, `APP_KEY`, `CORS_ALLOWED_ORIGINS`.
- **Module scaffolding**: `php create_module.php ModuleName` generates full CRUD module with Interface, Repository, CacheDecorator, UseCases, BaseRequest, msg, Provider.
- **Permission enum** at `app/Modules/Rol/Enums/Permission.php` — `create_module.php` auto-adds CRUD cases.
- **AuditLogger**: `App\Modules\Audit\Services\AuditLogger` — static methods: `logAuthentication()`, `logUserAction()`, `log()`.
