<?php

namespace App\Modules\Audit\Requests;

class GetAuditLogsRequest extends BaseRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules(): array
  {
    return [
      'file' => 'nullable|string|regex:/^[a-zA-Z0-9._-]+\.log$/',
      'limit' => 'nullable|integer|min:1|max:100',
      'page' => 'nullable|integer|min:1',
    ];
  }

  /**
   * Get the validation action for messages.
   *
   * @return string
   */
  protected function getValidationAction(): string
  {
    return 'audit_logs';
  }
}
