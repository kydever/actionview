<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Request;

use App\Constants\ErrorCode;
use Hyperf\Validation\Request\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'principal' => 'required',
            'description' => 'string',
            'status' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => (string) ErrorCode::PROJECT_NAME_CANNOT_BE_EMPTY,
            'principal.required' => (string) ErrorCode::PROJECT_PRINCIPAL_CANNOT_EMPTY,
        ];
    }
}
