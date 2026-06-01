<?php

use App\Modules\Session\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Session
 *
 * Este archivo define las rutas para las operaciones
 * del módulo Session.
 */

Route::get('/', [SessionController::class, 'index'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:session.read',
]);
Route::delete('/{id}', [SessionController::class, 'destroy'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:session.delete',
]);

Route::get('/check', [SessionController::class, 'check'])->middleware([
  'throttle:session',
  'jwt.cookie',
  'active.session',
]);
