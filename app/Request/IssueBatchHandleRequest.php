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
use App\Exception\BusinessException;
use Hyperf\Validation\Request\FormRequest;

class IssueBatchHandleRequest extends FormRequest
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
        return match ($this->getInputMethod()) {
            'update' => [
                'data' => 'required',
                'data.ids' => 'required|array',
                'data.values' => 'required|array',
            ],
            'delete' => [
                'data' => 'required',
                'data.ids' => 'required|array',
            ],
            default => throw new BusinessException(ErrorCode::ISSUE_BATCH_HANDLE_METHOD_INVALID),
        };
    }

    public function getInputMethod(): string
    {
        return strtolower($this->input('method'));
    }
}
