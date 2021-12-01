<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class WikiCreateRequest extends FormRequest
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
            'd' => 'string',
            'project_key' => 'string',
            'parent' => 'string',
            'name' => 'string',
            'contents' => 'string',
            'isSendMsg' => 'string',
            'v' => 'string',
        ];
    }
}
