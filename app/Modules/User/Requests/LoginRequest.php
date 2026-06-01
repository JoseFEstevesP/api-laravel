<?php

namespace App\Modules\User\Requests;

use App\Modules\User\msg\useMsg;

class LoginRequest extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'login';
  }

  public function rules(): array
  {
    return [
      'email' => ['required', 'string', 'email'],
      'password' => ['required', 'string'],
    ];
  }

  public function messages(): array
  {
    return [
      'email.required' => useMsg::get('validation.login.email.required'),
      'email.email' => useMsg::get('validation.email.email'),
      'password.required' => useMsg::get('validation.login.password.required'),
    ];
  }
}
