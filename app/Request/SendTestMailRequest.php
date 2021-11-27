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

class SendTestMailRequest extends FormRequest
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
            'to' => 'required',
            'subject' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'to.required' => (string) ErrorCode::MAIL_RECIPIENTS_CANNOT_BE_EMPTY,
            'subject.required' => (string) ErrorCode::MAIL_SUBJECT_CANNOT_BE_EMPTY,
        ];
    }
}
