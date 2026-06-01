<?php

namespace App\Console\Commands;

use App\Jobs\UpdateInactiveUsers;
use Illuminate\Console\Command;

class ProcessInactiveUsers extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'users:process-inactive';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Actualizar el estado de usuarios inactivos (más de 3 meses sin acceso) a inactivo (I)';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $this->info('Procesando usuarios inactivos...');

    // Disparar el job para actualizar usuarios inactivos
    UpdateInactiveUsers::dispatch();

    $this->info(
      'Job para actualizar usuarios inactivos enviado correctamente.',
    );

    return Command::SUCCESS;
  }
}
