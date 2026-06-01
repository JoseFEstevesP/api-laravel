<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| This file is responsible for loading the routes from all the modules
| in the app/Modules directory. It automatically prefixes the routes
| with "api" and the module name in lowercase.
|
| For example, the routes in the "User" module will be prefixed with "api/user".
|
*/

Route::prefix('api')->group(function () {
  foreach (File::directories(app_path('Modules')) as $module) {
    $routes_path = $module . '/routes.php';
    if (File::exists($routes_path)) {
      // Automatically prefix routes with the module name in lowercase
      // e.g., app/Modules/User -> api/user
      Route::prefix(strtolower(basename($module)))->group($routes_path);
    }
  }
});
