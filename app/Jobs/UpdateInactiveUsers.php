<?php

namespace App\Jobs;

use App\Modules\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateInactiveUsers implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct()
  {
  }

  public function handle(): void
  {
    $haceTresMeses = now()->subMonths(3);

    $usuariosActualizados = User::where(function ($query) use ($haceTresMeses) {
      $query
        ->where('dataOfAttempt', '<', $haceTresMeses)
        ->whereNotNull('dataOfAttempt');
    })
      ->orWhere(function ($query) use ($haceTresMeses) {
        $query
          ->whereNull('dataOfAttempt')
          ->where('created_at', '<', $haceTresMeses);
      })
      ->where('status', true)
      ->update(['status' => false]);

    \Illuminate\Support\Facades\Log::info(
      'Trabajo UpdateInactiveUsers completado: ' .
        $usuariosActualizados .
        ' usuarios actualizados a status inactivo',
    );
  }
}
