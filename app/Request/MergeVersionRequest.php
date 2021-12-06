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

class MergeVersionRequest extends FormRequest
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
            'dest' => 'required|integer',
            'source' => 'required|integer',
        ];
    }

    public function getMessages(): array
    {
        return [
            'source.required' => ErrorCode::VERSION_MERGE_SOURCE_CANNOT_EMPTY,
            'dest.required' => ErrorCode::VERSION_MERGE_DEST_CANNOT_EMPTY,
        ];
    }
}
