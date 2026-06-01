<?php

namespace App\Modules\Audit\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Requests\GetAuditLogsRequest;
use App\Modules\Audit\UseCases\GetAuditLogs;
use App\Modules\Audit\UseCases\GetAuditLogFiles;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para la gestión de logs de auditoría
 *
 * Maneja las operaciones de lectura de logs de auditoría
 * utilizando el patrón de casos de uso para separar la lógica de negocio
 * de la lógica de presentación (HTTP).
 */
class AuditLogController extends Controller
{
  private GetAuditLogs $getAuditLogsUseCase;
  private GetAuditLogFiles $getAuditLogFilesUseCase;

  public function __construct(
    GetAuditLogs $getAuditLogsUseCase,
    GetAuditLogFiles $getAuditLogFilesUseCase,
  ) {
    $this->getAuditLogsUseCase = $getAuditLogsUseCase;
    $this->getAuditLogFilesUseCase = $getAuditLogFilesUseCase;
  }

  /**
   * Obtener logs de auditoría de un archivo específico
   *
   * @param GetAuditLogsRequest $request
   * @return JsonResponse
   */
  public function getLogs(GetAuditLogsRequest $request): JsonResponse
  {
    return $this->getAuditLogsUseCase->execute($request);
  }

  /**
   * Obtener lista de archivos de logs de auditoría disponibles
   *
   * @return JsonResponse
   */
  public function getLogFiles(): JsonResponse
  {
    return $this->getAuditLogFilesUseCase->execute();
  }
}
