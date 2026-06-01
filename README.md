# Facturación IVSS API

API REST para el sistema de facturación del IVSS con autenticación JWT.

## Inicio Rápido (PostgreSQL - default)

```bash
# Clonar repositorio
git clone https://github.com/gato99/apppostgre_API.git
cd apppostgre_API

# Instalar dependencias
composer install
pnpm install

# Copiar configuración
cp .env.example .env

# Generar claves
php artisan key:generate
php artisan jwt:secret

# Iniciar contenedor
docker compose up -d --build

# Ejecutar migraciones
docker compose exec app php artisan migrate
```

## Configuración de Entorno

### Variables Principales

| Variable    | Descripción     | Desarrollo | Producción   |
| ----------- | --------------- | ---------- | ------------ |
| `APP_LOCAL` | Entorno local   | `true`     | `false`      |
| `APP_DEBUG` | Modo debug      | `true`     | `false`      |
| `APP_ENV`   | Entorno Laravel | `local`    | `production` |

### Base de Datos (PostgreSQL - default)

| Variable        | Descripción   | Desarrollo            | Producción     |
| --------------- | ------------- | --------------------- | -------------- |
| `DB_CONNECTION` | Driver        | `pgsql`               | `pgsql`        |
| `DB_HOST`       | Host          | `db`                  | IP remota      |
| `DB_PORT`       | Puerto        | `5432`                | según servidor |
| `DB_DATABASE`   | Base de datos | `apppostgre`    | según servidor |
| `DB_USERNAME`   | Usuario       | `appuser`             | según servidor |
| `DB_PASSWORD`   | Contraseña    | -                     | según servidor |
| `DB_SCHEMA`     | Schema        | `public`              | `public`       |

> **Para usar Oracle**, consulta la sección [Usar Oracle en lugar de PostgreSQL](#usar-oracle-en-lugar-de-postgresql) más abajo.

### Redis

| Variable       | Descripción  | Valor typical |
| -------------- | ------------ | ------------- |
| `REDIS_HOST`   | Host Redis   | `redis`       |
| `REDIS_PORT`   | Puerto Redis | `6379`        |
| `REDIS_CLIENT` | Cliente      | `predis`      |

### Autenticación JWT

| Variable                             | Descripción                                      |
| ------------------------------------ | ------------------------------------------------ |
| `JWT_SECRET`                         | Clave JWT (generar con `php artisan jwt:secret`) |
| `JWT_REFRESH_SECRET`                 | Clave para refresh token                         |
| `JWT_TTL`                            | Tiempo de vida token (minutos)                   |
| `JWT_REFRESH_TTL`                    | Tiempo de vida refresh (minutos)                 |
| `ACCESS_TOKEN_EXPIRATION_IN_MINUTES` | Expiración access token                          |
| `REFRESH_TOKEN_EXPIRATION_IN_DAYS`   | Expiración refresh token                         |

### Sesión

| Variable           | Descripción       | Valor typical |
| ------------------ | ----------------- | ------------- |
| `SESSION_DRIVER`   | Driver            | `redis`       |
| `SESSION_LIFETIME` | Vida sesión (min) | `120`         |

### CORS

| Variable               | Descripción         | Desarrollo              | Producción |
| ---------------------- | ------------------- | ----------------------- | ---------- |
| `CORS_ALLOWED_ORIGINS` | Orígenes permitidos | `http://localhost:5173` | producción |

## Ejecutar

```bash
# Iniciar servicios
docker compose up -d --build

# Ver logs
docker compose logs -f app

# Ejecutar migraciones
docker compose exec app php artisan migrate

# Detener servicios
docker compose down
```

## Tecnologías

- Laravel 10+ | PostgreSQL 16 | Redis 8 | Vue.js | JWT

---

## Usar Oracle en lugar de PostgreSQL

El proyecto está configurado para PostgreSQL por defecto, pero conserva compatibilidad total con Oracle Database (11g+). Para cambiar a Oracle:

### 1. Configurar variables de entorno

En tu `.env`:

```env
# Usar Oracle en lugar de PostgreSQL
DB_CONNECTION=oracle
DB_HOST=db
DB_PORT=1521
DB_DATABASE=XE
DB_USERNAME=SECURITY
DB_PASSWORD=security123
DB_SCHEMA=SECURITY
```

### 2. Restaurar dependencia Oracle

Agregar `yajra/laravel-oci8` a `composer.json`:

```json
"require": {
    ...
    "predis/predis": "^3.2",
    "tymon/jwt-auth": "^2.2",
    "yajra/laravel-oci8": "10.0"
}
```

Luego ejecutar:

```bash
composer update
```

### 3. Restaurar ServiceProvider

En `config/app.php`, agregar `Yajra\Oci8\Oci8ServiceProvider::class` al array `providers`:

```php
'providers' => ServiceProvider::defaultProviders()
    ->merge([
        ...
        App\Modules\Session\Providers\SessionServiceProvider::class,
        Yajra\Oci8\Oci8ServiceProvider::class,
    ])
    ->toArray(),
```

### 4. Revertir migraciones

Las migraciones de los módulos (`app/Modules/*/Migrations/`) están escritas para PostgreSQL. Para Oracle se requiere:

1. Revertir el `search_path` y usar `Schema::connection('oracle')` con prefijo `SECURITY.`
2. Habilitar secuencias y triggers para auto-increment (ver migraciones originales en git history)
3. Usar `char(1)` en lugar de `boolean` para campos como `activo` e `is_active`
4. Cambiar timestamp columns a sintaxis Oracle

### 5. Revertir `app/Console/Commands/OracleFreshCommand.php`

El comando `oracle:fresh` se eliminó al migrar a PostgreSQL. Para restaurarlo, recuperar el archivo del git history:

```bash
git checkout HEAD~1 -- app/Console/Commands/OracleFreshCommand.php
```

### 6. Modelos

Las tablas usan el esquema `SECURITY.` en Oracle. Los modelos (`$table`) se adaptan automáticamente según la conexión activa.

### 7. Volumen Docker

Para Oracle, cambiar el volumen en `docker-compose.yml` de `postgres-data` a `oracle-data` y usar la imagen `gvenzl/oracle-xe:11-slim-faststart`.

### docker compose with Oracle

```bash
# Para desarrollo con Oracle local
docker compose --profile local up -d
```

## Documentación

- **[Módulo User](docs/modules/user.md)** - Autenticación y usuarios
- **[Módulo Rol](docs/modules/rol.md)** - Roles y permisos (RBAC)
- **[Módulo Session](docs/modules/session.md)** - Gestión de sesiones
- **[Módulo Ip](docs/modules/ip.md)** - Control de IPs
- **[Módulo Audit](docs/modules/audit.md)** - Auditoría
- **[Módulo Facturación](docs/modules/facturacion.md)** - Proceso de post-facturación
- **[Logging](docs/system/logging_system.md)** - Sistema de registros
- **[Limpieza de Sesiones](docs/system/session_cleanup_cron_job.md)** - Tareas programadas
