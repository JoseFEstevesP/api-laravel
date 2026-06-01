<?php

namespace App\Modules\Rol\Requests;

use App\Modules\Rol\Enums\Permission;
use App\Modules\Rol\msg\useMsg;

class CreateRolRequest extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'create';
  }

  public function rules(): array
  {
    $validPermissions = array_column(Permission::cases(), 'value');

    return [
      'name' => [
        'required',
        'string',
        'min:5',
        'unique:roles,name',
      ],
      'description' => [
        'required',
        'string',
        'min:5',
      ],
      'permissions' => [
        'required',
        'array',
        'min:1',
      ],
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
      'name.required' => useMsg::get('validation.register.nombre.required'),
      'name.string' => useMsg::get('validation.register.nombre.string'),
      'name.min' => useMsg::get('validation.register.nombre.min'),
      'name.unique' => useMsg::get('validation.register.nombre.unique'),
      'description.required' => useMsg::get('validation.register.descripcion.required'),
      'description.string' => useMsg::get('validation.register.descripcion.string'),
      'description.min' => useMsg::get('validation.register.descripcion.min'),
      'permissions.required' => useMsg::get('validation.register.permisos.required'),
      'permissions.array' => useMsg::get('validation.register.permisos.array'),
      'permissions.min' => useMsg::get('validation.register.permisos.min'),
      'permissions.*.in' => useMsg::get('validation.register.permisos.in'),
    ];
  }
}
