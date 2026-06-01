<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Modules\Rol\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements JWTSubject
{
  use HasFactory, Notifiable, HasPermissions;

  protected $table = 'users';

  protected $primaryKey = 'uid';

  public function getKeyName()
  {
    return 'uid';
  }

  public $incrementing = false;

  public $timestamps = true;

  protected $keyType = 'string';

  protected $fillable = [
    'uid',
    'names',
    'surnames',
    'phone',
    'email',
    'password',
    'provider',
    'status',
    'code',
    'activatedAccount',
    'attemptCount',
    'dataOfAttempt',
    'uidRol',
  ];

  protected $visible = [
    'uid',
    'names',
    'surnames',
    'email',
    'phone',
    'status',
    'activatedAccount',
    'uidRol',
    'role',
  ];

  protected $with = ['role'];

  protected $appends = ['role_name'];

  public function getAuthIdentifierName()
  {
    return 'uid';
  }

  public function getAuthIdentifier()
  {
    return $this->{$this->getAuthIdentifierName()};
  }

  public function getAuthPassword()
  {
    return $this->password;
  }

  public function setEmailAttribute($value)
  {
    $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
  }

  public function getEmailAttribute($value)
  {
    return $value ? strtolower($value) : null;
  }

  protected $casts = [
    'status' => 'boolean',
    'activatedAccount' => 'boolean',
    'attemptCount' => 'integer',
    'dataOfAttempt' => 'datetime',
  ];

  public function role(): BelongsTo
  {
    return $this->belongsTo(
      \App\Modules\Rol\Models\Rol::class,
      'uidRol',
      'uid',
    );
  }

  public function getRoleNameAttribute()
  {
    return $this->role ? $this->role->name : null;
  }

  public function getJWTIdentifier()
  {
    return $this->uid;
  }

  public function getJWTCustomClaims()
  {
    $permissions = $this->role && $this->role->status
      ? (is_string($this->role->permissions) ? json_decode($this->role->permissions, true) : $this->role->permissions)
      : [];

    return [
      'uid' => $this->uid,
      'email' => $this->email,
      'provider' => $this->provider,
      'uidRol' => $this->uidRol,
      'permissions' => $permissions ?: [],
    ];
  }
}
