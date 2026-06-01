<?php

return [
  'validation' => [
    'audit_logs' => [
      'file' => [
        'regex' =>
          'El formato del nombre de archivo es inválido. Solo se permiten caracteres alfanuméricos, puntos, guiones bajos y guiones, y debe terminar con .log',
      ],
      'limit' => [
        'integer' => 'El límite debe ser un número entero.',
        'min' => 'El límite debe ser al menos 1.',
        'max' => 'El límite no puede ser mayor a 1000.',
      ],
    ],
    'general' => [
      'file' => [
        'regex' => 'El formato del nombre de archivo es inválido.',
      ],
      'limit' => [
        'integer' => 'El límite debe ser un número entero.',
        'min' => 'El límite debe ser al menos 1.',
        'max' => 'El límite no puede ser mayor a 1000.',
      ],
    ],
  ],
];
