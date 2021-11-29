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
use GuzzleHttp\RequestOptions;
use Han\Utils\Service;
use Hyperf\Config\Annotation\Value;
use Hyperf\Utils\Filesystem\Filesystem;

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

        di()->get(Filesystem::class)->makeDirectory(dirname($path), 0755, true, true);

        $client = new Client([
            'base_uri' => $this->domain,
        ]);
        $response = $client->get($object, [
            RequestOptions::SINK => $path,
            RequestOptions::HTTP_ERRORS => false,
        ]);

        if ($response->getStatusCode() === 200) {
            return $path;
        }

        return BASE_PATH . '/storage/hyperf.png';
    }
}
