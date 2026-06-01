# Documentación del Módulo de Roles (Rol)

## Descripción General

El módulo de Roles (`Rol`) es una parte fundamental del sistema de gestión de permisos basado en roles (RBAC - Role-Based Access Control) del sistema IVSS. Este módulo permite la creación, administración y asignación de roles a usuarios, así como la definición de permisos específicos que cada rol puede tener dentro del sistema.

## Arquitectura del Módulo

analiza @app/Modules/Rol/ y el midelware que se usa pra las rutas
dame un archivo explicando todo el modulo de su funcionamiento,
genrea el archiv md en la raiz del proyecto

El módulo sigue un patrón de arquitectura limpia (Clean Architecture) con los siguientes componentes principales:

### 1. Controlador (RolController)

El controlador `RolController` maneja todas las solicitudes HTTP relacionadas con los roles. Implementa los patrones CRUD (Crear, Leer, Actualizar, Eliminar) y otros endpoints específicos:

- `index()`: Lista todos los roles con posibilidad de filtrado y paginación
- `getFormat()`: Obtiene roles en formato específico para selects u otras interfaces
- `getActiveRoles()`: Devuelve solo roles activos
- `show($id)`: Muestra un rol específico por ID
- `create()`: Crea un nuevo rol
- `update($id)`: Actualiza un rol existente
- `destroy($id)`: Elimina un rol

### 2. Casos de Uso (Use Cases)

Cada operación del controlador delega su lógica de negocio a un caso de uso correspondiente:

- `FindAllRol`: Recupera todos los roles con filtros y paginación
- `FindAllFormatRol`: Recupera roles en formato específico
- `FindByIdRol`: Busca un rol por ID
- `CreateRol`: Crea un nuevo rol con auditoría
- `UpdateRol`: Actualiza un rol existente con auditoría
- `DeleteRol`: Elimina un rol con auditoría
- `FindActiveRoles`: Recupera roles activos

### 3. Modelo (Rol)

El modelo `Rol` representa la entidad de rol en la base de datos:

- Tabla: `SECURITY.ROLES`
- Atributos: `id`, `nombre`, `descripcion`, `permisos`, `activo`
- Relación: Uno a Muchos con Usuarios (`users`)
- Accesores: Convierte los permisos de JSON a array y viceversa
- Validación: Verifica que los permisos sean válidos según el enum `Permission`

### 4. Repositorios

- `RolRepositoryInterface`: Define la interfaz de operaciones de datos
- `RolRepository`: Implementación concreta de operaciones CRUD
- `RolRepositoryCacheDecorator`: Implementación con caché para mejorar el rendimiento

### 5. Servicios

- `PermissionService`: Valida si un usuario tiene un permiso específico

### 6. Middleware

- `PermissionMiddleware`: Middleware que verifica si un usuario tiene permiso para acceder a una ruta específica

## Sistema de Permisos (RBAC)

### Enum de Permisos

El sistema utiliza un enum `Permission` que define todos los permisos disponibles:

```php
enum Permission: string
{
  // Sistema
  case SUPER = 'super';
  // Usuarios
  case USER = 'user';
  case USER_READ = 'user.read';
  case USER_CREATE = 'user.create';
  case USER_UPDATE = 'user.update';
  case USER_DELETE = 'user.delete';
  // Rol
  case ROL = 'rol';
  case ROL_READ = 'rol.read';
  case ROL_CREATE = 'rol.create';
  case ROL_UPDATE = 'rol.update';
  case ROL_DELETE = 'rol.delete';
  // IP
  case IP = 'ip';
  case IP_READ = 'ip.read';
  case IP_CREATE = 'ip.create';
  case IP_UPDATE = 'ip.update';
  case IP_DELETE = 'ip.delete';
  // Audit
  case AUDIT_READ = 'audit.read';
  // Session
  case SESSION_READ = 'session.read';
  case SESSION_DELETE = 'session.delete';
}
```

### Middleware de Permisos

El `PermissionMiddleware` verifica si el usuario autenticado tiene el permiso requerido para acceder a una ruta:

```php
public function handle(Request $request, Closure $next, string $permission): Response {
    if (app()->runningInConsole()) {
      return $next($request);
    }

    $user = Auth::user();
    if (!$user || !$user->hasPermission($permission)) {
      abort(403, 'Acceso denegado');
    }

    return $next($request);
  }
```

### Trait HasPermissions

Este trait se aplica al modelo de Usuario y proporciona métodos para verificar permisos:

- `hasPermission(string $permission)`: Verifica si el usuario tiene un permiso específico
- `hasAnyPermission(array $permissions)`: Verifica si el usuario tiene al menos uno de los permisos especificados
- Optimizado para leer permisos directamente del payload JWT antes de recurrir a la base de datos

## Rutas del Módulo

Las rutas están definidas en `routes.php` y utilizan múltiples middlewares:

- `GET /`: Listar roles (middleware: `jwt.cookie`, `active.session`, `permission:rol.read`)
- `GET /formato`: Roles en formato específico (middleware: `jwt.cookie`, `active.session`)
- `GET /activos`: Roles activos (middleware: `jwt.cookie`, `active.session`, `permission:rol.read`)
- `GET /{id}`: Mostrar rol específico (middleware: `jwt.cookie`, `active.session`, `permission:rol.read`)
- `POST /`: Crear rol (middleware: `jwt.cookie`, `active.session`, `permission:rol.create`)
- `PUT /{id}`: Actualizar rol (middleware: `jwt.cookie`, `active.session`, `permission:rol.update`)
- `DELETE /{id}`: Eliminar rol (middleware: `jwt.cookie`, `active.session`, `permission:rol.delete`)

## Base de Datos

### Tablas

1. `SECURITY.ROLES`:
   - `id`: Identificador único del rol
   - `nombre`: Nombre del rol (único)
   - `descripcion`: Descripción del rol
   - `permisos`: Array de permisos en formato JSON
   - `activo`: Estado del rol (1=activo, 0=inactivo)

2. `SECURITY.USUARIOS` (relación):
   - `role_id`: Foreign key que referencia a `SECURITY.ROLES.id`

### Migraciones

- `2025_12_14_030557_create_roles_table.php`: Crea la tabla de roles
- `2025_12_14_035251_add_role_id_to_users_table.php`: Agrega columna role_id a usuarios

## Validación de Peticiones

El módulo incluye clases de solicitud específicas para validar entradas:

- `CreateRolRequest`: Valida la creación de roles
- `UpdateRolRequest`: Valida la actualización de roles
- `BaseRequest`: Clase base con reglas comunes de validación

## Mensajes de Sistema

Los mensajes del sistema están centralizados en `msg/msg.php` y gestionados por `useMsg.php` para facilitar la internacionalización.

## Auditoría

Todas las operaciones de creación, actualización y eliminación de roles registran eventos en el sistema de auditoría para mantener un historial de cambios.

## Proveedor de Servicios

`RolServiceProvider` registra las dependencias del módulo, configura el middleware de permisos y gestiona el decorador de caché para mejorar el rendimiento.

## Características Técnicas

- **Patrón Repository**: Abstrae la lógica de acceso a datos
- **Patrón Decorator**: Implementa caché sin modificar la lógica base
- **Validación de Datos**: Reglas específicas para cada operación
- **Auditoría Completa**: Registro de todas las operaciones importantes
- **Caché Inteligente**: Mejora el rendimiento de operaciones frecuentes
- **Seguridad Robusta**: Sistema de permisos basado en roles
- **JWT Integration**: Soporte para tokens JWT con permisos embebidos
