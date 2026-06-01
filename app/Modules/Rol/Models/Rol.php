<?php

namespace App\Modules\Rol\Models;

use App\Modules\Rol\Enums\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Rol extends Model
{
  protected $table = 'roles';

  protected $primaryKey = 'uid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = ['uid', 'name', 'description', 'permissions', 'status'];

  protected $visible = ['uid', 'name', 'description', 'permissions', 'status'];

  public function users(): HasMany
  {
    return $this->hasMany(\App\Modules\User\Models\User::class, 'uidRol', 'uid');
  }

  public function scopeActive($query)
  {
    return $query->where('status', true);
  }

  protected function permissions(): Attribute
  {
    return Attribute::make(
      get: fn($value) => json_decode($value, true) ?: [],
      set: fn($value) => json_encode(is_array($value) ? $value : []),
    );
  }

  protected function status(): Attribute
  {
    return Attribute::make(
      get: fn($value) => (bool) $value,
      set: fn($value) => $value ? true : false,
    );
  }

  protected static function boot(): void
  {
    parent::boot();
    static::saving(function ($role) {
      $validPermissions = array_column(Permission::cases(), 'value');
      $permissions = is_string($role->permissions)
        ? json_decode($role->permissions, true)
        : $role->permissions;
      foreach ($permissions as $perm) {
        if (!in_array($perm, $validPermissions)) {
          throw new \InvalidArgumentException("Permiso inválido: $perm");
        }
      }
    });
  }
}
