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
namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use GuzzleHttp\Client;
use Han\Utils\Service;
use Hyperf\Config\Annotation\Value;

class FileService extends Service
{
    #[Value(key: 'file.domain')]
    protected string $domain;

    public function getAvatar(string $object): string
    {
        if (empty($this->domain)) {
            throw new BusinessException(ErrorCode::FILE_DOMAIN_INVALID);
        }

        $path = BASE_PATH . '/runtime/' . $object;
        if (file_exists($path)) {
            return $path;
        }

        $client = new Client([
            'base_uri' => $this->domain,
        ]);
        $client->get($object, [
            'sink' => $path,
        ]);

        if (! file_exists($path)) {
            throw new BusinessException(ErrorCode::FILE_DOMAIN_INVALID);
        }

        return $path;
    }
}
