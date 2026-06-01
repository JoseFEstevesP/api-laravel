<?php

namespace App\Modules\Rol\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
  abstract protected function getValidationAction(): string;

  protected function prepareForValidation(): void
  {
    if ($this->has('permissions') && is_string($this->permissions)) {
      $decoded = json_decode($this->permissions, true);
      if (is_array($decoded)) {
        $this->merge(['permissions' => $decoded]);
      }
    }
  }

  public function authorize(): bool
  {
    return true;
  }
}
