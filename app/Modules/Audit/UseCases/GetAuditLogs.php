<?php

namespace App\Modules\Audit\UseCases;

use App\Modules\Audit\Requests\GetAuditLogsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Caso de uso para obtener logs de auditoría de un archivo específico
 *
 * Maneja la lógica de negocio para leer y procesar archivos de logs de auditoría,
 * parsear su contenido y devolverlo en formato estructurado.
 */
class GetAuditLogs
{
  /**
   * Ejecuta la operación de obtención de logs de auditoría
   *
   * @param GetAuditLogsRequest $request
   * @return JsonResponse
   */
  public function execute(GetAuditLogsRequest $request): JsonResponse
  {
    try {
      // Obtener el nombre del archivo desde la solicitud o usar el predeterminado
      $fileName = $request->get('file', 'audit-' . date('Y-m-d') . '.log');

      $logPath = storage_path('logs/' . $fileName);

      // Verificar si el archivo existe
      if (!File::exists($logPath)) {
        return response()->json(
          [
            'all' => [
              'message' => 'El archivo de registro no existe: ' . $fileName,
            ],
          ],
          404,
        );
      }

      // Parámetros de paginación
      $perPage = $request->get('limit', 50); // Valor por defecto
      $page = $request->get('page', 1); // Valor por defecto

      // Calcular offset
      $offset = ($page - 1) * $perPage;

      // Leer el contenido del archivo de log
      $content = File::get($logPath);

      // Parsear el contenido del log en datos estructurados
      $allLogs = $this->parseLogContent($content);

      // Aplicar paginación
      $paginatedLogs = array_slice($allLogs, $offset, $perPage);

      // Contar total de registros
      $total = count($allLogs);
      $lastPage = ceil($total / $perPage);

      $data = [
        'rows' => $paginatedLogs,
        'count' => $total,
        'currentPage' => $page,
        'nextPage' => $page < $lastPage && $total > 0 ? $page + 1 : null,
        'previousPage' => $page > 1 && $total > 0 ? $page - 1 : null,
        'limit' => $perPage,
        'pages' => $lastPage,
        'file' => $fileName,
        'path' => $logPath,
        'size' => File::size($logPath),
      ];

      return response()->json($data);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' =>
              'No se pudo leer el archivo de registro: ' . $e->getMessage(),
          ],
        ],
        500,
      );
    }
  }

  /**
   * Parsear contenido de log en datos estructurados
   *
   * @param string $content
   * @return array
   */
  private function parseLogContent(string $content): array
  {
    $lines = explode("\n", $content);
    $logs = [];

    foreach ($lines as $line) {
      if (trim($line) === '') {
        continue;
      }

      // Intentar parsear como JSON primero (si ya está estructurado)
      $logEntry = json_decode($line, true);
      if ($logEntry !== null && json_last_error() === JSON_ERROR_NONE) {
        $logs[] = $logEntry;
        continue;
      }

      // Si no es JSON, intentar parsear como formato de log de Laravel
      $parsedLog = $this->parseLaravelLogLine($line);
      if ($parsedLog) {
        $logs[] = $parsedLog;
      }
    }

    return $logs;
  }

  /**
   * Parsear una sola línea de log de Laravel
   *
   * @param string $line
   * @return array|null
   */
  private function parseLaravelLogLine(string $line): ?array
  {
    // Formato de log de Laravel: [2023-01-01 12:00:00] local.INFO: message {"context": "data"}
    $pattern =
      '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([^\.]+)\.([^:]+): (.*?)(?=\s{(\s*{.*})\s*}$|$)/s';

    if (preg_match($pattern, $line, $matches)) {
      $timestamp = $matches[1];
      $env = $matches[2];
      $level = $matches[3];
      $message = trim($matches[4]);

      // Extraer contexto si está presente
      $context = [];
      $contextStart = strpos($line, '{');
      if ($contextStart !== false) {
        $contextStr = substr($line, $contextStart);
        $context = json_decode($contextStr, true);
        if ($context === null) {
          $context = [];
        }
      }

      return [
        'timestamp' => $timestamp,
        'environment' => $env,
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'raw' => $line,
      ];
    }

    // Si no coincide con el formato Laravel, devolver como crudo
    return [
      'timestamp' => null,
      'level' => 'UNKNOWN',
      'message' => $line,
      'context' => [],
      'raw' => $line,
    ];
  }
}
