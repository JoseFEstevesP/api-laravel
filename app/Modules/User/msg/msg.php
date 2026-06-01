<?php

return [
  'auth' => [
    'invalid_credentials' => 'Credenciales incorrectas.',
    'logged_in' => 'Se ha conectado correctamente',
    'logout_success' => 'Sesión cerrada correctamente',
    'logout_error' => 'Error al cerrar sesión',
    'login_failed' => 'Error al iniciar sesión',
    'not_authenticated' => 'Usuario no autenticado',
    'session_already_exists' =>
      'Ya existe una sesión activa para este usuario. Por favor, cierre la sesión anterior o contacte al administrador.',
    'user_blocked' =>
      'El usuario está bloqueado. Por favor contacte con un administrador.',
    'user_invalid_status' => 'Estado del usuario no válido.',
  ],

  'register' => [
    'success' => 'Usuario registrado correctamente',
    'error' => 'Error registrando usuario',
  ],

  'User' => [
    'created' => 'Usuario creado con éxito',
    'retrieved' => 'Usuarios recuperados con éxito',
    'updated' => 'Usuario actualizado con éxito',
    'deleted' => 'Usuario eliminado con éxito',
    'not_found' => 'Usuario no encontrado',
    'creation_error' => 'Error al crear usuario',
    'retrieval_error' => 'Error al recuperar usuarios',
    'update_error' => 'Error al actualizar usuario',
    'deletion_error' => 'Error al eliminar usuario',
    'retrieved_one' => 'Usuario recuperado con éxito',
    'not_found_one' => 'Usuario no encontrado',
  ],

  'token' => [
    'refresh_missing' => 'Error cerrando sesión.',
    'invalid_or_expired' => 'Refresh token inválido o expirado.',
    'access_refreshed' => 'Access token renovado correctamente',
    'refresh_error' => 'Error al renovar token',
  ],

  'validation' => [
    'general' => [
      'names' => [
        'required' => 'El campo nombres es obligatorio.',
        'string' => 'El campo nombres debe ser una cadena de texto.',
        'min' => 'El campo nombres debe tener al menos 2 caracteres.',
        'max' => 'El campo nombres no debe exceder los 255 caracteres.',
      ],
      'surnames' => [
        'required' => 'El campo apellidos es obligatorio.',
        'string' => 'El campo apellidos debe ser una cadena de texto.',
        'min' => 'El campo apellidos debe tener al menos 2 caracteres.',
        'max' => 'El campo apellidos no debe exceder los 255 caracteres.',
      ],
      'password' => [
        'required' => 'El campo contraseña es obligatorio.',
        'string' => 'El campo contraseña debe ser una cadena de texto.',
        'min' => 'El campo contraseña debe tener al menos 8 caracteres.',
        'max' => 'El campo contraseña no debe exceder los 20 caracteres.',
        'regex' =>
          'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un símbolo.',
      ],
      'phone' => [
        'required' => 'El campo teléfono es obligatorio.',
        'string' => 'El campo teléfono debe ser una cadena de texto.',
        'regex' => 'El formato del teléfono es incorrecto. ej: 04140000000.',
      ],
      'email' => [
        'required' => 'El campo de correo electrónico es obligatorio.',
        'string' =>
          'El campo de correo electrónico debe ser una cadena de texto.',
        'email' =>
          'El campo de correo electrónico debe ser una dirección de correo electrónico válida.',
        'max' =>
          'El campo de correo electrónico no debe exceder los 255 caracteres.',
        'unique' => 'El correo electrónico ya está en uso.',
      ],
      'uidRol' => [
        'required' => 'El campo rol es obligatorio.',
        'exists' => 'El rol seleccionado no existe.',
      ],
      'status' => [
        'boolean' => 'El campo estado debe ser un valor booleano.',
      ],
    ],
    'login' => [
      'email' => [
        'required' => 'El campo correo electrónico es obligatorio.',
        'string' => 'El campo correo electrónico debe ser una cadena de texto.',
        'email' => 'Debe proporcionar un correo electrónico válido.',
      ],
      'password' => [
        'required' => 'El campo contraseña es obligatorio.',
      ],
    ],
  ],
];
