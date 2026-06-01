<?php

namespace App\Modules\User\Requests;

use App\Modules\User\msg\useMsg;

class RegisterRequest extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'register';
  }

  protected function allowedFields(): array
  {
    return ['names', 'surnames', 'phone', 'email', 'password', 'uidRol'];
  }

  public function rules(): array
  {
    return [
      'names' => ['required', 'string', 'min:2', 'max:255'],
      'surnames' => ['required', 'string', 'min:2', 'max:255'],
      'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/'],
      'uidRol' => ['required', 'string', 'exists:roles,uid'],
    ];
  }

  public function messages(): array
  {
    return [
      'names.required' => 'El campo nombres es obligatorio.',
      'names.min' => 'El campo nombres debe tener al menos 2 caracteres.',
      'surnames.required' => 'El campo apellidos es obligatorio.',
      'surnames.min' => 'El campo apellidos debe tener al menos 2 caracteres.',
      'phone.required' => 'El campo teléfono es obligatorio.',
      'phone.regex' => 'El formato del teléfono es incorrecto. ej: 04140000000.',
      'email.required' => 'El campo correo electrónico es obligatorio.',
      'email.email' => 'El campo correo electrónico debe ser una dirección válida.',
      'email.unique' => 'El correo electrónico ya está en uso.',
      'password.required' => 'El campo contraseña es obligatorio.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.max' => 'La contraseña no debe exceder los 20 caracteres.',
      'password.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un símbolo.',
      'uidRol.required' => 'El campo rol es obligatorio.',
      'uidRol.exists' => 'El rol seleccionado no existe.',
    ];
  }
}
