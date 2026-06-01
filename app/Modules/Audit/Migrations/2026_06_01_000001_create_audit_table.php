<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('audit', function (Blueprint $table) {
      $table->uuid('uid')->primary();
      $table->uuid('uidUser');
      $table->text('refreshToken');
      $table->json('dataToken');
      $table->timestamps();

      $table->index('uidUser', 'idx_audit_uid_user');
      $table->index('created_at', 'idx_audit_created_at');
      $table->index(['uidUser', 'created_at'], 'idx_audit_user_created');

      $table->foreign('uidUser')
        ->references('uid')
        ->on('users');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('audit');
  }
};
