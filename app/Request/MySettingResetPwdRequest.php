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

class MySettingResetPwdRequest extends FormRequest
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
            'password' => 'required|string',
            'new_password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => (string) ErrorCode::PASSWORD_OLD_NOT_EMPTY,
            'new_password.required' => (string) ErrorCode::PASSWORD_NOT_EMPTY,
        ];
    }
}
