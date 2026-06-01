# Documentación del Módulo de Sesiones (Session)

## Descripción General

El módulo de Sesiones (`Session`) gestiona las sesiones activas de usuarios en el sistema. Permite verificar sesiones concurrentes, gestionar el cierre de sesiones y mantener un registro de la actividad del usuario.

## Arquitectura del Módulo

### Componentes Principales

```
app/Modules/Session/
├── Controllers/         # Manejo de solicitudes HTTP
├── Models/             # Modelo UserSession
├── Repositories/       # Acceso a datos
├── UseCases/           # Lógica de negocio
├── Migrations/         # Migraciones de base de datos
├── msg/                # Mensajes del sistema
├── routes.php          # Definición de rutas
└── Providers/          # Proveedor de servicios
```

### Modelo (UserSession)

- **Tabla**: `SECURITY.USER_SESSIONS`
- **Atributos principales**:
  - `id`: Identificador único
  - `user_id`: ID del usuario
  - `session_id`: ID de la sesión
  - `refresh_token`: Token de refresco
  - `ip_address`: Dirección IP
  - `user_agent`: Agente de usuario
  - `login_at`: Momento de inicio
  - `last_activity`: Última actividad
  - `expires_at`: Fecha de expiración
  - `is_active`: Estado de la sesión

### Índices Existentes

| Índice                          | Descripción             |
| ------------------------------- | ----------------------- |
| `idx_user_sessions_user_id`     | Búsqueda por usuario    |
| `idx_user_sessions_session_id`  | Búsqueda por sesión     |
| `idx_user_sessions_active`      | Filtrado de activas     |
| `idx_user_sessions_user_active` | Usuario + estado        |
| `idx_user_sessions_expires_at`  | Limpieza de expiradas   |
| `idx_user_sessions_login_at`    | Ordenamiento y limpieza |

## Métodos del Modelo

### Scopes

```php
// Sesiones activas
UserSession::active()->get();

// Sesiones por usuario
UserSession::byUser($userId)->get();

// Por ID de sesión
UserSession::bySessionId($sessionId)->first();

// Por refresh token
UserSession::byRefreshToken($token)->first();
```

### Métodos Estáticos

```php
// Verificar si hay sesión activa
UserSession::hasActiveSession($userId): bool

// Obtener sesión activa
UserSession::getActiveSession($userId): ?UserSession

// Cerrar todas las sesiones de un usuario
UserSession::closeAllActiveSessions($userId): int

// Limpiar sesiones expiradas
UserSession::cleanExpiredSessions($hoursAfterExpiry = 24): int

// Eliminar sesiones antiguas
UserSession::deleteOldSessions($maxAgeInDays = 30): int
```

## Rutas del Módulo

| Método | Ruta    | Middleware                                              | Descripción     |
| ------ | ------- | ------------------------------------------------------- | --------------- |
| GET    | `/`     | `jwt.cookie, active.session`                            | Listar sesiones |
| GET    | `/{id}` | `jwt.cookie, active.session`                            | Ver sesión      |
| DELETE | `/{id}` | `jwt.cookie, active.session, permission:session.delete` | Eliminar sesión |

## Base de Datos

### Tabla: SECURITY.USER_SESSIONS

```sql
CREATE TABLE SECURITY.USER_SESSIONS (
  id NUMBER(19) PRIMARY KEY,
  user_id VARCHAR2(255),
  session_id VARCHAR2(255),
  refresh_token VARCHAR2(512),
  ip_address VARCHAR2(45),
  user_agent VARCHAR2(1023),
  login_at TIMESTAMP,
  last_activity TIMESTAMP,
  expires_at TIMESTAMP,
  is_active CHAR(1) DEFAULT '1',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Índices

Los índices fueron creados en la migración `2026_02_19_000000_add_indexes_rol_session_tables.php`:

- `idx_user_sessions_user_id` - Para búsquedas por usuario
- `idx_user_sessions_session_id` - Para búsquedas por sesión
- `idx_user_sessions_active` - Para filtrado rápido
- `idx_user_sessions_user_active` - Para consultas combinadas
- `idx_user_sessions_expires_at` - Para limpieza de expiradas
- `idx_user_sessions_login_at` - Para ordenamiento

## Casos de Uso

### FindAllSession

- Lista todas las sesiones con filtros y paginación
- Filtros: `status`, `search`, `orderProperty`, `order`

### FindByIdSession

- Busca una sesión por ID

### DeleteSession

- Elimina una sesión por ID

### CleanExpiredSessions

- Limpia sesiones expiradas e inactivas

## Middleware

### ActiveSessionMiddleware

Verifica que el usuario tenga una sesión activa válida:

```php
public function handle($request, Closure $next)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    $hasActiveSession = UserSession::hasActiveSession($user->id_usuario);
    if (!$hasActiveSession) {
        return response()->json(['message' => 'Sesión no activa'], 401);
    }

    return $next($request);
}
```

## Limpieza de Sesiones

### Comando Artisan

```bash
php artisan sessions:clean
```

### Programación (Cron)

El comando puede ejecutarse periódicamente para limpiar sesiones expiradas:

```bash
# Limpiar sesiones expiradas cada hora
0 * * * * php /path/to/artisan sessions:clean >> /dev/null 2>&1
```

### Proceso de Limpieza

1. Elimina sesiones inactivas con más de 24 horas de inactividad
2. Elimina sesiones activas que han excedido su fecha de expiración
3. Elimina sesiones con más de 30 días de antigüedad

## Seguridad

- Las sesiones incluyen IP y User Agent para trazabilidad
- Tokens de refreso para renovación de autenticación
- Fecha de expiración configurable (por defecto 24 horas)
- Auditoría de actividades de sesión

## Integración con JWT

El módulo Session se integra con el sistema de autenticación JWT:

1. Al iniciar sesión, se crea un registro en `USER_SESSIONS`
2. El token JWT incluye el `session_id` en el payload
3. El middleware verifica la validez de la sesión antes de cada request
4. Al cerrar sesión, se marca como inactiva o se elimina el registro
