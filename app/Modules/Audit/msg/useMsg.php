<?php

namespace App\Modules\Audit\msg;

/**
 * Clase useMsg - Gestor de mensajes de localización para el módulo Audit
 *
 * Proporciona métodos estáticos para cargar y recuperar mensajes traducidos
 * desde archivos de idioma ubicados en app/Modules/Audit/msg/
 */
class useMsg
{
  /**
   * Cache cargada de mensajes por locale para el módulo
   * @var array<string, array>
   */
  protected static array $cache = [];

  /**
   * Obtener mensaje del módulo Audit desde app/Modules/Audit/msg/msg.php
   * Ejemplo: useMsg::get('validation.audit_logs.file.regex')
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

    $message = self::getMessage(self::$cache[$locale], $key);

    // Reemplazar placeholders si se proporcionan
    foreach ($replace as $placeholder => $replacement) {
      $message = str_replace(':' . $placeholder, $replacement, $message);
    }

    return $message;
  }

  /**
   * Recuperar un mensaje específico desde un array anidado
   *
   * @param array $messages
   * @param string $key
   * @return string
   */
  private static function getMessage(array $messages, string $key): string
  {
    $keys = explode('.', $key);
    $current = $messages;

    foreach ($keys as $k) {
      if (!is_array($current) || !array_key_exists($k, $current)) {
        return $key; // Devolver la clave original si no se encuentra el mensaje
      }
      $current = $current[$k];
    }

    return is_string($current) ? $current : $key;
  }
}
