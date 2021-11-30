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

abstract class FormRequest extends \Hyperf\Validation\Request\FormRequest
{
    public function messages(): array
    {
        $messages = [];
        foreach ($this->getMessages() as $key => $code) {
            $messages[$key] = (string) $code;
        }
        return $messages;
    }

    abstract public function getMessages(): array;
}
