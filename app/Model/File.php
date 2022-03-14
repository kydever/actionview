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
namespace App\Model;

/**
 * @property int $id
 * @property string $name 文件名
 * @property int $size 文件大小
 * @property string $type 文件类型
 * @property array $uploader 上传人信息
 * @property string $index 文件位置索引
 * @property string $thumbnails_index 缩略图文件索引
 * @property int $del_flg 删除状态
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class File extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'file';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'size', 'type', 'uploader', 'index', 'thumbnails_index', 'del_flg', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'size' => 'integer', 'uploader' => 'json', 'del_flg' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
