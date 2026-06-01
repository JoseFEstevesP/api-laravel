<?php

return [
  'Rol' => [
    'created' => 'Rol creado con éxito',
    'retrieved' => 'Rol recuperado con éxito',
    'updated' => 'Rol actualizado con éxito',
    'deleted' => 'Rol eliminado con éxito',
    'not_found' => 'Rol no encontrado',
    'creation_error' => 'Error al crear Rol',
    'retrieval_error' => 'Error al recuperar Rol',
    'update_error' => 'Error al actualizar Rol',
    'deletion_error' => 'Error al eliminar Rol',
  ],
  'validation' => [
    'register' => [
      'nombre' => [
        'required' => 'El campo nombre es obligatorio',
        'string' => 'El campo nombre debe ser de tipo string',
        'min' => 'El campo nombre debe tener al menos 5 caracteres',
        'unique' => 'El nombre ya existe',
      ],
      'descripcion' => [
        'required' => 'El campo descripcion es obligatorio',
        'string' => 'El campo descripcion debe ser de tipo string',
        'min' => 'El campo descripcion debe tener al menos 5 caracteres',
      ],
      'permisos' => [
        'required' => 'El campo permisos es obligatorio',
        'array' => 'El campo permisos debe ser de tipo array',
        'min' => 'El campo permisos debe contener al menos un elemento',
        'in' => 'Permiso no válido.',
        'string' => 'El campo permisos debe ser de tipo string',
      ],
    ],
    'update' => [
      'activo' => [
        'boolean' => 'El campo activo debe ser de tipo boolean',
      ],
    ],
  ],
];
