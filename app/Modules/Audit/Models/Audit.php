<?php

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
  protected $table = 'audit';

  protected $primaryKey = 'uid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = [
    'uid',
    'uidUser',
    'refreshToken',
    'dataToken',
  ];

  protected $casts = [
    'dataToken' => 'array',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(
      \App\Modules\User\Models\User::class,
      'uidUser',
      'uid',
    );
  }
}
