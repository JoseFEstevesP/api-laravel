<?php

namespace App\Modules\Rol\msg;

/**
 * Clase useMsg - Gestor de mensajes de localización para el módulo Rol
 *
 * Proporciona métodos estáticos para cargar y recuperar mensajes traducidos
 * desde archivos de idioma ubicados en app/Modules/Rol/msg/
 */
class useMsg
{
  /**
   * Cache cargada de mensajes por locale para el módulo
   * @var array<string, array>
   */
  protected static array $cache = [];

  /**
   * Obtener mensaje del módulo Rol desde app/Modules/Rol/msg/msg.php
   * Ejemplo: useMsg::get('Rol.not_found')
   *
   * @param string $key
   * @param array $replace
   * @return string
   */
  public static function get(string $key, array $replace = []): string
  {
    $locale = 'es';

    if (!isset(self::$cache[$locale])) {
      $file = __DIR__ . '/msg.php';

      if (file_exists($file)) {
        self::$cache[$locale] = require $file;
      } else {
        self::$cache[$locale] = [];
      }
    }

    $parts = explode('.', $key);
    $data = self::$cache[$locale];

    foreach ($parts as $part) {
      if (!is_array($data) || !array_key_exists($part, $data)) {
        return $key;
      }
      $data = $data[$part];
    }

    $message = is_string($data) ? $data : $key;

    // Reemplazo de placeholders: :nombre_variable
    foreach ($replace as $placeholder => $value) {
      $message = str_replace(':' . $placeholder, $value, $message);
    }

    return $message;
  }
}
