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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Filesystem\Filesystem;
use League\Flysystem\FilesystemOperator;

class FileService extends Service
{
    #[Value(key: 'file.domain')]
    protected string $domain;

    #[Inject]
    protected FilesystemOperator $file;

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
            RequestOptions::HTTP_ERRORS => false,
        ]);

        $response = $client->get($object);

        if ($response->getStatusCode() === 200) {
            file_put_contents($path, $response->getBody());
            return $path;
        }

        return BASE_PATH . '/storage/hyperf.png';
    }

    /**
     * @param UploadedFile[] $files
     */
    public function upload(array $files)
    {
        foreach ($files as $file) {
            $path = $this->safeMove($file);

            $this->file->writeStream();
        }

        return [];
    }

    public function safeMove(UploadedFile $file): string
    {
        $dir = BASE_PATH . '/runtime/uploads/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->moveTo($path = $dir . uniqid());

        return $path;
    }
}
