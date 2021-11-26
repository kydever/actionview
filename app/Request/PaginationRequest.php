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

use Hyperf\Validation\Request\FormRequest;

class PaginationRequest extends FormRequest
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
            'page' => 'integer',
            'limit' => 'integer',
        ];
    }

    public function offset(): int
    {
        $limit = $this->limit();
        $page = (int) $this->input('page', 1);
        $page = max(1, $page);

        return $limit * ($page - 1);
    }

    public function limit(): int
    {
        return (int) $this->input('page_size', 30);
    }
}
