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
use App\Model\File;
use App\Model\User;
use App\Service\Dao\IssueDao;
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
    public function upload(array $files, User $user, int $issueId)
    {
        $models = [];
        $issue = di()->get(IssueDao::class)->first($issueId, true);
        $attachments = $issue->attachments;

        foreach ($files as $file) {
            $local = $this->safeMove($file);
            $info = pathinfo($file->getClientFilename());
            $extension = $info['extension'] ?? null;
            if (empty($extension)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR, '上传文件类型非法');
            }

            $path = format_uploaded_path(uniqid() . '.' . $extension);
            $this->file->writeStream($path, fopen($local, 'r+'));
            $models[] = $model = $this->createFile($path, $file, $user);
            $attachments[] = $model->id;
        }

        $issue->save();

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

    protected function createFile(string $path, UploadedFile $file, User $user): File
    {
        $model = new File();
        $model->index = $path;
        $model->thumbnails_index = $path;
        $model->type = $file->getClientMediaType();
        $model->name = $file->getClientFilename();
        $model->size = $file->getSize();
        $model->uploader = $user->toSmall();
        $model->save();

        return $model;
    }
}
