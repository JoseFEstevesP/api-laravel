# Documentación del Proyecto - Facturación IVSS API

## Índice General

### Guía de Desarrollo

- **[AGENTS.md](../AGENTS.md)** - Directrices para agentes de desarrollo

### Módulos del Sistema

- **[Módulo User](modules/user.md)** - Autenticación y gestión de usuarios
- **[Módulo Rol](modules/rol.md)** - Sistema de roles y permisos (RBAC)
- **[Módulo Session](modules/session.md)** - Gestión de sesiones de usuario
- **[Módulo Audit](modules/audit.md)** - Sistema de auditoría

### Arquitectura y Patrones

- **[Estrategia de Migraciones](architecture/migration_strategy.md)** - Gestión de migraciones de DB
- **[Plan de Implementación](architecture/implementation_plan.md)** - Detalles del proyecto

### Sistema

- **[Logging](system/logging_system.md)** - Sistema de registros
- **[Limpieza de Sesiones](system/session_cleanup_cron_job.md)** - Tareas programadas
- **[Validación](system/validation_metrics.md)** - Métricas de validación

---

## Estructura del Proyecto

```
├── app/
│   └── Modules/
│       ├── User/          # Usuarios y autenticación
│       ├── Rol/           # Roles y permisos (RBAC)
│       ├── Session/       # Sesiones de usuario
│       └── Audit/         # Auditoría
├── database/
│   └── migrations/       # Migraciones
├── docs/
│   ├── modules/          # Documentación de módulos
│   ├── architecture/     # Documentación de arquitectura
│   ├── system/          # Documentación del sistema
│   └── README.md        # Este archivo
└── tests/               # Pruebas
```

## Comenzando

1. **Configuración**: Copiar `.env.example` a `.env` y configurar
2. **Dependencias**: `composer install`
3. **Migraciones**: `php artisan migrate`
4. **Servidor**: `php artisan serve`

## Comandos Útiles

```bash
# Desarrollo
php artisan serve
./vendor/bin/pint

# Base de datos
php artisan migrate
php artisan migrate:fresh --seed

# Pruebas
php artisan test
```

## Tecnologías

- Laravel 10+ | Oracle Database | JWT | Redis
