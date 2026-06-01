<?php

namespace App\Modules\Audit\UseCases;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

/**
 * Caso de uso para obtener la lista de archivos de logs de auditoría disponibles
 *
 * Maneja la lógica de negocio para listar los archivos de logs de auditoría
 * disponibles en el directorio de logs.
 */
class GetAuditLogFiles
{
  /**
   * Ejecuta la operación de obtención de la lista de archivos de logs
   *
   * @return JsonResponse
   */
  public function execute(): JsonResponse
  {
    try {
      $logDirectory = storage_path('logs');
      $files = File::glob($logDirectory . '/audit-*.log');

      $logFiles = [];
      foreach ($files as $file) {
        $fileName = basename($file);
        $logFiles[] = [
          'name' => $fileName,
          'path' => $file,
          'size' => File::size($file),
          'modified' => File::lastModified($file),
          'modified_formatted' => date(
            'Y-m-d H:i:s',
            File::lastModified($file),
          ),
        ];
      }

      // Ordenar por tiempo de modificación (más reciente primero)
      usort($logFiles, function ($a, $b) {
        return $b['modified'] - $a['modified'];
      });

      return response()->json([
        'files' => $logFiles,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' =>
              'No se pudieron enumerar los archivos de registro: ' .
              $e->getMessage(),
          ],
        ],
        500,
      );
    }
  }
}
