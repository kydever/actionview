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
    public const SERVER_ERROR = -99999;

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
     * @Message("用户组名称不能为空")
     */
    public const GROUP_NAME_NOT_EMPTY = -10200;

    /**
     * @Message("用户组不存在")
     */
    public const GROUP_NOT_EXSIT = -10201;

    /**
     * @Message("该小组来自外部董事会")
     */
    public const GROUP_FROM_EXTERNAL_DIRECTION = -10203;

    /**
     * @Message("问题类型必填")
     */
    public const ISSUE_TYPE_NOT_EMPTY = -11100;

    /**
     * @Message("当前问题类型不存在 Schema")
     */
    public const ISSUE_TYPE_SCHEMA_NOT_EXIST = -11101;

    /**
     * @Message("TimeTracking 非法")
     */
    public const ISSUE_TIME_TRACKING_INVALID = -11102;

    /**
     * @Message("负责人没有 assigned-issue 权限")
     */
    public const ASSIGNED_USER_PERMISSION_DENIED = -11118;

    /**
     * @Message("当前问题类型存在必填的字段")
     */
    public const ISSUE_TYPE_SCHEMA_REQUIRED = -11121;

    /**
     * @Message("日期选择器格式非法")
     */
    public const ISSUE_DATE_TIME_PICKER_INVALID = -11122;

    /**
     * @Message("角色非法")
     */
    public const ROLE_INVALID = -12701;

    /**
     * @Message("角色不存在")
     */
    public const ROLE_NOT_EXISTS = -12702;

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
     * @Message("项目不存在")
     */
    public const PROJECT_NOT_EXIST = -14004;

    /**
     * @Message("项目 principal 必填")
     */
    public const PROJECT_PRINCIPAL_CANNOT_EMPTY = -14005;

    /**
     * @Message("项目已被废弃")
     */
    public const PROJECT_ARCHIVED = -14009;

    /**
     * @Message("用户不存在")
     */
    public const USER_NOT_EXISTS = -15000;

    /**
     * @Message("旧密码不能为空")
     */
    public const PASSWORD_OLD_NOT_EMPTY = -15001;

    /**
     * @Message("密码不正确")
     */
    public const PASSWORD_INCORRECT = -15002;

    /**
     * @Message("密码不能为空")
     */
    public const PASSWORD_NOT_EMPTY = -15003;

    /**
     * @Message("个人资料姓名不能为空")
     */
    public const FIRST_NAME_NOT_EXIST = -15005;

    /**
     * @Message("上传的头像不能为空")
     */
    public const AVATAR_CANNOT_EMPTY = -15006;

    /**
     * @Message("头像格式非法")
     */
    public const AVATAR_TYPE_INVALID = -15007;

    /**
     * @Message("头像ID不能为空")
     */
    public const AVATAR_ID_NOT_EMPTY = -15100;

    /**
     * @Message("邮件发送失败")
     */
    public const MAIL_SEND_FAILED = -15200;

    /**
     * @Message("邮件接收者不允许为空")
     */
    public const MAIL_RECIPIENTS_CANNOT_BE_EMPTY = -15201;

    /**
     * @Message("邮件标题不允许为空")
     */
    public const MAIL_SUBJECT_CANNOT_BE_EMPTY = -15202;

    /**
     * @Message("邮件服务配置有误")
     */
    public const MAIL_INVALID = -15203;

    /**
     * @Message("文件系统 Domain 设置非法")
     */
    public const FILE_DOMAIN_INVALID = -17000;

    /**
     * @Message("父目录不能为空")
     */
    public const PARENT_NOT_EMPTY = -11950;

    /**
     * @Message("父目录不存在")
     */
    public const PARENT_NOT_EXIST = -11951;

    /**
     * @Message("WIKI名称不能为空")
     */
    public const WIKI_NAME_NOT_EMPTY = -11952;

    /**
     * @Message("WIKI名称不能重复")
     */
    public const WIKI_NAME_NOT_REPEAT = -11953;

    /**
     * @Message("WIKI对象不存在")
     */
    public const WIKI_OBJECT_NOT_EXIST = -11954;

}
