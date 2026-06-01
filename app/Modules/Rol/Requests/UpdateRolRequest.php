<?php

namespace App\Modules\Rol\Requests;

use App\Modules\Rol\Enums\Permission;
use App\Modules\Rol\msg\useMsg;

class UpdateRolRequest extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'update';
  }

  public function rules(): array
  {
    $rol = $this->route('uid');
    $validPermissions = array_column(Permission::cases(), 'value');

    return [
      'name' => [
        'string',
        'min:5',
        'unique:roles,name,' . $rol . ',uid',
      ],
      'description' => ['string', 'min:5'],
      'permissions' => ['array', 'min:1'],
      'permissions.*' => [
        'string',
        'in:' . implode(',', $validPermissions),
      ],
      'status' => ['boolean'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.string' => useMsg::get('validation.register.nombre.string'),
      'name.min' => useMsg::get('validation.register.nombre.min'),
      'name.unique' => useMsg::get('validation.register.nombre.unique'),
      'description.string' => useMsg::get('validation.register.descripcion.string'),
      'description.min' => useMsg::get('validation.register.descripcion.min'),
      'permissions.array' => useMsg::get('validation.register.permisos.array'),
      'permissions.min' => useMsg::get('validation.register.permisos.min'),
      'permissions.*.in' => useMsg::get('validation.register.permisos.in'),
      'status.boolean' => useMsg::get('validation.update.activo.boolean'),
    ];
  }
}
