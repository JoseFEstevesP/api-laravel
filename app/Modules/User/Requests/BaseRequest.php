<?php

namespace App\Modules\User\Requests;

use App\Modules\User\msg\useMsg;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
  abstract protected function getValidationAction(): string;

  protected function prepareForValidation(): void
  {
    $input = $this->all();

    if (isset($input['email'])) {
      $input['email'] = strtolower(trim($input['email']));
    }

    $this->replace($this->sanitize($input));
  }

  protected function sanitize(array $data): array
  {
    $allowedFields = $this->allowedFields();

    if (empty($allowedFields)) {
      return $data;
    }

    return array_intersect_key($data, array_flip($allowedFields));
  }

  protected function allowedFields(): array
  {
    return [];
  }

  public function authorize(): bool
  {
    return true;
  }

  protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
  {
    $errors = $validator->errors()->toArray();
    $formatted = [];

    foreach ($errors as $field => $messages) {
      $formatted[$field] = ['message' => $messages[0]];
    }

    throw new \Illuminate\Validation\ValidationException(
      $validator,
      response()->json($formatted, 422),
    );
  }
}
