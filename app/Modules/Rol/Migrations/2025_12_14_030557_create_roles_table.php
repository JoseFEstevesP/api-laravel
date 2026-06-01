<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('roles', function (Blueprint $table) {
      $table->uuid('uid')->primary();
      $table->string('name', 255)->unique();
      $table->string('description', 255);
      $table->json('permissions');
      $table->boolean('status')->default(true);
      $table->timestamps();

      $table->index('status', 'idx_role_status');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('roles');
  }
};
