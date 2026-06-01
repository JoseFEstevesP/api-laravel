<?php

use App\Modules\Rol\Controllers\RolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RolController::class, 'index'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:rol.read',
]);
Route::get('/{uid}', [RolController::class, 'show'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:rol.read',
]);
Route::post('/', [RolController::class, 'store'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:rol.create',
]);
Route::put('/{uid}', [RolController::class, 'update'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:rol.update',
]);
Route::delete('/{uid}', [RolController::class, 'destroy'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:rol.delete',
]);
