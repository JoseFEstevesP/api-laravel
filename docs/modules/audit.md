# Documentación del Módulo de Auditoría (Audit)

## Descripción General

El módulo de Auditoría (`Audit`) proporciona un sistema centralizado de registro de eventos del sistema. Permite rastrear acciones de usuarios, eventos de seguridad y cambios en el sistema.

## Arquitectura del Módulo

```
app/Modules/Audit/
├── Controllers/        # AuditLogController
├── Services/           # AuditLogger
├── UseCases/           # GetAuditLogs, GetAuditLogFiles
├── Listeners/          # Auth/SecurityAuthListener
├── Requests/           # GetAuditLogsRequest
├── msg/                # Mensajes del sistema
└── Providers/          # AuditServiceProvider
```

## Servicio de Auditoría (AuditLogger)

El `AuditLogger` proporciona métodos estáticos para registrar eventos:

### Métodos Principales

```php
// Registrar evento de auditoría
AuditLogger::log(
  string $event,
  array $context = [],
  ?string $userId = null,
  ?string $resourceType = null,
  ?string $resourceId = null,
  ?string $description = null
): void

// Registrar evento de seguridad
AuditLogger::logSecurity(
  string $event,
  array $context = [],
  ?string $userId = null,
  ?string $description = null
): void

// Registrar acción de usuario
AuditLogger::logUserAction(
  string $action,
  ?string $userId,
  ?string $resourceType = null,
  ?string $resourceId = null,
  array $details = []
): void

// Registrar evento de autenticación
AuditLogger::logAuthentication(
  string $eventType,
  ?string $userId = null,
  array $details = []
): void
```

### Estructura del Log

Cada evento incluye:

- `timestamp` - Fecha y hora ISO
- `event` - Nombre del evento
- `user_id` - ID del usuario
- `resource_type` - Tipo de recurso
- `resource_id` - ID del recurso
- `description` - Descripción
- `context` - Datos adicionales
- `ip_address` - IP del cliente
- `user_agent` - Agente de usuario
- `url` - URL solicitada
- `method` - Método HTTP

## Canales de Log

El sistema utiliza múltiples canales de log:

| Canal      | Propósito                              |
| ---------- | -------------------------------------- |
| `audit`    | Eventos generales de auditoría         |
| `security` | Eventos de seguridad de alta prioridad |
| `stack`    | Log principal de la aplicación         |

## Rutas del Módulo

| Método | Ruta                   | Middleware                            | Descripción            |
| ------ | ---------------------- | ------------------------------------- | ---------------------- |
| GET    | `/api/audit/logs`      | `jwt, session, permission:audit.read` | Ver logs de auditoría  |
| GET    | `/api/audit/log-files` | `jwt, session, permission:audit.read` | Listar archivos de log |

## Permisos del Módulo

- `audit.read` - Ver logs de auditoría

## Eventos de Seguridad Rastreados

### Autenticación

- `auth.login` - Inicio de sesión exitoso
- `auth.logout` - Cierre de sesión
- `auth.failed_login` - Fallo de autenticación
- `auth.token_refresh` - Renovación de token

### Acceso

- `access.granted` - Acceso permitido
- `access.denied` - Acceso denegado
- `access.ip_blocked` - IP bloqueada

### Cambios

- `user.created` - Usuario creado
- `user.updated` - Usuario actualizado
- `user.deleted` - Usuario eliminado
- `role.changed` - Rol modificado
- `permissions.modified` - Permisos cambiados

## Integración con el Sistema

### Listeners

El módulo incluye listeners para eventos de autenticación:

- `SecurityAuthListener` - Escucha eventos auth para registrar en auditoría

### Uso en otros módulos

```php
use App\Modules\Audit\Services\AuditLogger;

// Registrar acción
AuditLogger::logUserAction('user.login', $user->id_usuario, 'auth', null, [
  'ip' => request()->ip(),
]);

// Registrar evento de seguridad
AuditLogger::logSecurity(
  'failed_login',
  [
    'reason' => 'contraseña incorrecta',
  ],
  $userId,
);
```

## Retención de Logs

Los logs de auditoría se almacenan en:

- Archivos diarios en `storage/logs/audit/`
- Canales configurados en `config/logging.php`

La retención debe configurarse según políticas de la organización.
