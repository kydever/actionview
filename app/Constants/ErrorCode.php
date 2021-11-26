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
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("邮箱或密码错误")
     */
    public const USER_PASSWORD_INVALID = -10000;

    /**
     * @Message("登录态已经失效，请重新登录")
     */
    public const TOKEN_INVALID = -10001;

    /**
     * @Message("您没有对应权限")
     */
    public const PERMISSION_DENIED = -10002;

    /**
     * @Message("邮箱或密码不能为空")
     */
    public const EMAIL_OR_PASSWORD_NOT_EXIST = -10003;

    /**
     * @Message("用户已被封禁")
     */
    public const USER_DISABLED = -10006;

    /**
     * @Message("昵称不能为空")
     */
    public const USER_NAME_NOT_EXIST = -10100;

    /**
     * @Message("邮箱不能为空")
     */
    public const EMAIL_NOT_EXIST = -10101;

    /**
     * @Message("邮箱已被注册")
     */
    public const EMAIL_ALREADY_REGISTERED = -10102;

    /**
     * @Message("密码不能为空")
     */
    public const PASSWORD_NOT_EXIST = -10103;

    /**
     * @Message("项目名称不能为空")
     */
    public const PROJECT_NAME_CANNOT_BE_EMPTY = -14000;

    /**
     * @Message("项目KEY不能为空")
     */
    public const PROJECT_KEY_CANNOT_BE_EMPTY = -14001;

    /**
     * @Message("项目KEY已被其他项目使用")
     */
    public const PROJECT_KEY_HAS_BEEN_TAKEN = -14002;

    /**
     * @Message("项目 principal 不存在")
     */
    public const PROJECT_PRINCIPAL_NOT_EXIST = -14003;

    /**
     * @Message("用户不存在")
     */
    public const USER_NOT_EXISTS = -15000;
}
