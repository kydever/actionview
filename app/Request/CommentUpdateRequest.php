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

class CommentUpdateRequest extends FormRequest
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
            'operation' => 'string|in:addReply,editReply,delReply',
            'contents' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'operation.in' => (string) ErrorCode::ISSUE_OPERATION_INVALID,
        ];
    }
}
