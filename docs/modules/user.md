# Documentación del Módulo de Usuarios (User)

## Descripción General

El módulo de Usuarios (`User`) gestiona la autenticación y administración de usuarios del sistema. Utiliza JWT para autenticación y se integra con el sistema de roles y sesiones.

## Arquitectura del Módulo

```
app/Modules/User/
├── Controllers/         # UserController
├── Models/             # User, UserSiraCiudadano
├── Repositories/       # UserRepository, UserRepositoryCacheDecorator
├── UseCases/           # LoginUser, LogoutUser, RefreshToken, etc.
├── Requests/           # LoginRequest, RegisterRequest, UpdateUserRequest
├── Services/           # RefreshTokenService
├── Traits/             # UserRequestInfoTrait
├── Migrations/        # Migraciones de base de datos
├── msg/                # Mensajes del sistema
└── Providers/          # UserServiceProvider
```

## Modelo (User)

- **Tabla**: `SECURITY.USUARIOS`
- **Primary Key**: `id_usuario` (string, no auto-incremental)
- **Implementa**: `JWTSubject` (Tymon/JWTAuth)

### Atributos

| Campo                 | Tipo     | Descripción                     |
| --------------------- | -------- | ------------------------------- |
| `id_usuario`          | string   | Identificador único del usuario |
| `cedula`              | string   | Cédula de identidad             |
| `clave`               | string   | Contraseña hasheada             |
| `email`               | string   | Email del usuario               |
| `telefono`            | string   | Teléfono de contacto            |
| `estatus`             | string   | Estado del usuario              |
| `role_id`             | int      | FK al rol del usuario           |
| `fecha_creacion`      | datetime | Fecha de creación               |
| `fecha_caducidad`     | datetime | Fecha de caducidad              |
| `fecha_ultimo_acceso` | datetime | Último acceso                   |
| `fecha_modificacion`  | datetime | Última modificación             |

### Relaciones

```php
// Relación con rol
public function role(): BelongsTo
{
  return $this->belongsTo(Rol::class, 'role_id', 'id');
}
```

### JWT Claims

```php
public function getJWTCustomClaims(): array
{
  return [
    'id_usuario' => $this->id_usuario,
    'cedula' => $this->cedula,
    'email' => $this->email,
    'fk_app_perfil' => $this->fk_app_perfil,
  ];
}
```

## Rutas del Módulo

### Autenticación (Públicas)

| Método | Ruta                | Middleware | Descripción       |
| ------ | ------------------- | ---------- | ----------------- |
| POST   | `/api/user/login`   | -          | Iniciar sesión    |
| POST   | `/api/user/refresh` | -          | Renovar token JWT |

### Usuarios (Protegidas)

| Método | Ruta                | Middleware                             | Descripción         |
| ------ | ------------------- | -------------------------------------- | ------------------- |
| GET    | `/api/user`         | `jwt, session, permission:user.read`   | Listar usuarios     |
| GET    | `/api/user/formato` | `jwt, session`                         | Usuarios en formato |
| POST   | `/api/user`         | `jwt, session, permission:user.create` | Crear usuario       |
| GET    | `/api/user/{id}`    | `jwt, session, permission:user.read`   | Ver usuario         |
| PUT    | `/api/user/{id}`    | `jwt, session, permission:user.update` | Actualizar usuario  |
| DELETE | `/api/user/{id}`    | `jwt, session, permission:user.delete` | Eliminar usuario    |
| POST   | `/api/user/logout`  | `jwt, session`                         | Cerrar sesión       |

## Casos de Uso

| UseCase        | Descripción                          |
| -------------- | ------------------------------------ |
| `LoginUser`    | Autentica usuario y genera token JWT |
| `LogoutUser`   | Cierra sesión del usuario            |
| `RefreshToken` | Renueva el token JWT                 |
| `RegisterUser` | Registra nuevo usuario               |
| `FindAllUser`  | Lista usuarios con filtros           |
| `FindUserById` | Busca usuario por ID                 |
| `CreateUser`   | Crea nuevo usuario                   |
| `UpdateUser`   | Actualiza usuario existente          |
| `DeleteUser`   | Elimina usuario                      |

## Autenticación JWT

### Flujo de Login

1. Usuario envía credenciales (`id_usuario`, `clave`)
2. Sistema valida credenciales contra `SECURITY.USUARIOS`
3. Si es válido, genera token JWT con claims personalizados
4. Crea registro de sesión en `SECURITY.USER_SESSIONS`
5. Devuelve token en cookie HTTP-only

### Refresh Token

- El token JWT tiene vida configurable
- Endpoint `/api/user/refresh` renueva el token
- Verifica que la sesión sigue activa

## Permisos del Módulo

Los permisos definidos en `Permission` enum:

- `user` - Acceso general al módulo
- `user.read` - Ver usuarios
- `user.create` - Crear usuarios
- `user.update` - Actualizar usuarios
- `user.delete` - Eliminar usuarios

## Seguridad

- Contraseñas almacenadas hasheadas con bcrypt
- Tokens JWT con cookies HTTP-only
- Validación de IP autorizada
- Sesiones con auditoría
  -Trait `HasPermissions` para verificación de permisos
