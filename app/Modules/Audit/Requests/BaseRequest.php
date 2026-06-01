<?php

namespace App\Modules\Audit\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Audit\msg\useMsg;

abstract class BaseRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function messages(): array
  {
    $messages = [];
    $action = $this->getValidationAction();
    $rules = $this->rules();

    foreach ($rules as $field => $fieldRules) {
      // Asegurarse de que las reglas sean un array
      $fieldRules = is_array($fieldRules) ? $fieldRules : [$fieldRules];

      foreach ($fieldRules as $rule) {
        if (is_string($rule)) {
          $ruleParts = explode(':', $rule);
          $ruleName = $ruleParts[0];

          // Intentar obtener el mensaje específico de la acción
          $messageKey = "validation.{$action}.{$field}.{$ruleName}";
          $message = useMsg::get($messageKey);

          // Si no se encuentra, usar el mensaje general
          if ($message === $messageKey) {
            $generalMessageKey = "validation.general.{$field}.{$ruleName}";
            $message = useMsg::get($generalMessageKey);
          }

          $messages["{$field}.{$ruleName}"] = $message;
        }
      }
    }
    return $messages;
  }

  abstract protected function getValidationAction(): string;
}
