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

class VersionReleaseRequest extends FormRequest
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
            'status' => 'required|in:released,unreleased,archived',
        ];
    }

    public function getMessages(): array
    {
        return [
            'status.in' => ErrorCode::VERSION_RELEASE_STATUS_INVALID,
            'status.required' => ErrorCode::VERSION_RELEASE_STATUS_CANNOT_EMPTY,
        ];
    }
}
