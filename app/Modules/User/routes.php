<?php

use App\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login'])->middleware('throttle:auth');
Route::post('/refresh', [UserController::class, 'refresh'])->middleware('throttle:auth-refresh');

Route::get('/', [UserController::class, 'index'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:user.read',
]);
Route::post('/', [UserController::class, 'register'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:user.create',
]);
Route::put('/{uid}', [UserController::class, 'update'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:user.update',
]);
Route::delete('/{uid}', [UserController::class, 'destroy'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
  'permission:user.delete',
]);
Route::post('/logout', [UserController::class, 'logout'])->middleware([
  'throttle:api',
  'jwt.cookie',
  'active.session',
]);
