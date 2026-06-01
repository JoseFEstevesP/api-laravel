<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionController extends Controller
{
  public function checkConnection()
  {
    try {
      DB::connection()->getPdo();
      return response()->json([
        'status' => 'success',
        'message' => __('messages.database.connected'),
      ]);
    } catch (\Exception $e) {
      Log::error('Database connection error: ' . $e->getMessage());
      return response()->json(
        [
          'status' => 'error',
          'message' => __('messages.database.connection_failed'),
          'error' => $e->getMessage(),
        ],
        500,
      );
    }
  }
}
