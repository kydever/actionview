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
namespace App\Service\Struct;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Hyperf\Utils\Filesystem\Filesystem;

class Image
{
    private array $sizeInfo;

    private Filesystem $filesystem;

    public function __construct(private string $path)
    {
        $this->sizeInfo = getimagesize($path);
        $this->filesystem = di()->get(Filesystem::class);
    }

    public static function makeFromBase64Data(string $data, string $dir): static
    {
        di()->get(Filesystem::class)->makeDirectory($dir, 0755, true, true);

        $filename = $dir . '/' . uniqid();

        file_put_contents($filename, base64_decode($data));

        return new static($filename);
    }

    public function toAvatarPath(): string
    {
        if (! is_array($this->sizeInfo)) {
            throw new BusinessException(ErrorCode::AVATAR_TYPE_INVALID);
        }

        if (! in_array($this->sizeInfo['mime'], $this->getMimes())) {
            throw new BusinessException(ErrorCode::AVATAR_TYPE_INVALID);
        }

        $width = $this->sizeInfo[0];
        $height = $this->sizeInfo[1];
        $scale = $width < $height ? $height : $width;
        $thumbnailsWidth = (int) floor(150 * $width / $scale);
        $thumbnailsHeight = (int) floor(150 * $height / $scale);
        $thumbnailsFilename = $this->getPathWithoutExtension() . '_resize.' . $this->getExtension();

        if ($scale <= 150) {
            $this->filesystem->copy($this->path, $thumbnailsFilename);
            return $thumbnailsFilename;
        }

        if ($this->isJPEG()) {
            $srcImage = imagecreatefromjpeg($this->path);
            $dstImage = imagecreatetruecolor($thumbnailsWidth, $thumbnailsHeight);
            imagecopyresized($dstImage, $srcImage, 0, 0, 0, 0, $thumbnailsWidth, $thumbnailsHeight, $width, $height);
            imagejpeg($dstImage, $thumbnailsFilename);
            return $thumbnailsFilename;
        }

        if ($this->isPNG()) {
            $srcImage = imagecreatefrompng($this->path);
            $dstImage = imagecreatetruecolor($thumbnailsWidth, $thumbnailsHeight);
            imagecopyresized($dstImage, $srcImage, 0, 0, 0, 0, $thumbnailsWidth, $thumbnailsHeight, $width, $height);
            imagepng($dstImage, $thumbnailsFilename);
            return $thumbnailsFilename;
        }

        if ($this->isGIF()) {
            $srcImage = imagecreatefromgif($this->path);
            $dstImage = imagecreatetruecolor($thumbnailsWidth, $thumbnailsHeight);
            imagecopyresized($dstImage, $srcImage, 0, 0, 0, 0, $thumbnailsWidth, $thumbnailsHeight, $width, $height);
            imagegif($dstImage, $thumbnailsFilename);
            return $thumbnailsFilename;
        }

        $this->filesystem->copy($this->path, $thumbnailsFilename);
        return $thumbnailsFilename;
    }

    public function getExtension(): string
    {
        $array = explode('.', $this->path);
        if (count($array) > 1) {
            return end($array);
        }

        $array = explode('/', $this->sizeInfo['mime']);

        return end($array);
    }

    protected function getPathWithoutExtension(): string
    {
        $array = explode('.', $this->path);
        if (count($array) === 1) {
            return $this->path;
        }

        array_pop($array);

        return implode('.', $array);
    }

    protected function isJPEG(): bool
    {
        return in_array($this->sizeInfo['mime'], ['image/jpeg', 'image/jpg']);
    }

    protected function isPNG(): bool
    {
        return $this->sizeInfo['mime'] === 'image/png';
    }

    protected function isGIF(): bool
    {
        return $this->sizeInfo['mime'] === 'image/gif';
    }

    protected function getMimes(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
        ];
    }
}
