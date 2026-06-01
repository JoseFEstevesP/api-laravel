<?php

namespace App\Modules\Session\msg;

/**
 * Clase useMsg - Gestor de mensajes de localización para el módulo Session
 *
 * Proporciona métodos estáticos para cargar y recuperar mensajes traducidos
 * desde archivos de idioma ubicados en app/Modules/Session/msg/
 */
class useMsg
{
  /**
   * Cache cargada de mensajes por locale para el módulo
   * @var array<string, array>
   */
  protected static array $cache = [];

  /**
   * Obtener mensaje del módulo Session desde app/Modules/Session/msg/msg.php
   * Ejemplo: useMsg::get('Session.retrieved')
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

    return is_string($data) ? $data : $key;
  }
}
