<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OwenIt\Auditing\Models\Audit;

class TestAuditCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'test:audit';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Test the audit functionality';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Testing audit functionality...');

    try {
      // Check if we can query the audits table
      $count = Audit::count();
      $this->info('Current audit count: ' . $count);

      // Try to create a test audit record
      $audit = Audit::create([
        'user_type' => 'App\\Modules\\User\\Models\\User',
        'user_id' => '1',
        'event' => 'test_event',
        'auditable_type' => 'test',
        'auditable_id' => '1',
        'old_values' => null,
        'new_values' => ['test' => 'value'],
        'url' => 'test_url',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test_agent',
        'tags' => 'test,audit',
      ]);

      $this->info('Successfully created audit with ID: ' . $audit->id);

      // Verify the record was saved
      $newCount = Audit::count();
      $this->info('New audit count: ' . $newCount);

      // Show the created audit
      $lastAudit = Audit::latest()->first();
      $this->info('Last audit event: ' . $lastAudit->event);
      $this->info('Last audit user_id: ' . $lastAudit->user_id);
      $this->info('Last audit tags: ' . $lastAudit->tags);

      $this->info('Audit functionality test completed successfully!');
    } catch (\Exception $e) {
      $this->error('Error: ' . $e->getMessage());
      $this->error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    }
  }
}
