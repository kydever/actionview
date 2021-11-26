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

class ProjectStoreRequest extends FormRequest
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
            'key' => 'required',
            'name' => 'required',
            'description' => 'required',
            'principal' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => (string) ErrorCode::PROJECT_NAME_CANNOT_BE_EMPTY,
            'key.required' => (string) ErrorCode::PROJECT_KEY_CANNOT_BE_EMPTY,
        ];
    }
}
