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

class CommentStoreRequest extends FormRequest
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
            'contents' => 'required',
            'atWho.*.id' => 'required|integer',
            'atWho.*.email' => 'required|string',
            'atWho.*.name' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'contents.required' => (string) ErrorCode::ISSUE_COMMENT_CONTENTS_NOT_EXIST,
        ];
    }
}
