<?php

use App\Modules\Audit\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Audit Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the Audit module.
| This route file is loaded by the application automatically.
|
*/

// Rutas de logs de auditoría (protegidas)
Route::get('/logs', [AuditLogController::class, 'getLogs'])
  ->middleware(['throttle:audit', 'jwt.cookie', 'active.session', 'permission:audit.read'])
  ->name('audit.logs');

Route::get('/log-files', [AuditLogController::class, 'getLogFiles'])
  ->middleware(['throttle:audit', 'jwt.cookie', 'active.session', 'permission:audit.read'])
  ->name('audit.log-files');
