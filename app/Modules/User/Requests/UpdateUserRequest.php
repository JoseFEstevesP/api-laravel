<?php

namespace App\Modules\User\Requests;

use App\Modules\User\msg\useMsg;

class UpdateUserRequest extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'update';
  }

  protected function allowedFields(): array
  {
    return [
      'names', 'surnames', 'phone', 'email', 'password',
      'status', 'code', 'activatedAccount', 'uidRol',
    ];
  }

  public function rules(): array
  {
    $uid = $this->route('uid');

    return [
      'names' => ['string', 'min:2', 'max:255'],
      'surnames' => ['string', 'min:2', 'max:255'],
      'phone' => ['string', 'regex:/^[0-9]{11}$/'],
      'email' => ['string', 'email', 'max:255', 'unique:users,email,' . $uid . ',uid'],
      'password' => ['string', 'min:8', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/'],
      'status' => ['boolean'],
      'code' => ['string', 'max:255'],
      'activatedAccount' => ['boolean'],
      'uidRol' => ['string', 'exists:roles,uid'],
    ];
  }

  public function messages(): array
  {
    return [
      'names.string' => 'El campo nombres debe ser una cadena de texto.',
      'names.min' => 'El campo nombres debe tener al menos 2 caracteres.',
      'surnames.string' => 'El campo apellidos debe ser una cadena de texto.',
      'surnames.min' => 'El campo apellidos debe tener al menos 2 caracteres.',
      'phone.regex' => 'El formato del teléfono es incorrecto. ej: 04140000000.',
      'email.email' => 'El campo correo electrónico debe ser una dirección válida.',
      'email.unique' => 'El correo electrónico ya está en uso.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.max' => 'La contraseña no debe exceder los 20 caracteres.',
      'password.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un símbolo.',
      'status.boolean' => 'El campo estado debe ser un valor booleano.',
      'activatedAccount.boolean' => 'El campo cuenta activada debe ser un valor booleano.',
      'uidRol.exists' => 'El rol seleccionado no existe.',
    ];
  }
}
