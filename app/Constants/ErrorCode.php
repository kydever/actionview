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
     * @Message("缺少必要参数")
     */
    public const MISSING_PARAMETER = -99998;

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
     * @Message("问题不存在或不属于此项目")
     */
    public const ISSUE_NOT_EXIST = -11103;

    /**
     * @Message("负责人必填")
     */
    public const ISSUE_ASSIGNEE_CANNOT_EMPTY = -11104;

    /**
     * @Message("问题过滤器名字必传")
     */
    public const ISSUE_FILTER_NAME_CANNOT_EMPTY = -11105;

    /**
     * @Message("ActionID 必填")
     */
    public const ISSUE_DO_ACTION_ID_CANNOT_EMPTY = -11115;

    /**
     * @Message("您没有分配负责人的权限")
     */
    public const ASSIGN_ASSIGNEE_DENIED = -11116;

    /**
     * @Message("您没有分配负责人的权限")
     */
    public const ASSIGNED_ASSIGNEE_DENIED = -11117;

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
     * @Message("批量操作非法")
     */
    public const ISSUE_BATCH_HANDLE_METHOD_INVALID = -11125;

    /**
     * @Message("评论内容不能为空")
     */
    public const ISSUE_COMMENT_CONTENTS_NOT_EXIST = -11200;

    /**
     * @Message("当前问题评论不存在")
     */
    public const ISSUE_DONT_HAVE_COMMENTS = -11201;

    /**
     * @Message("评论的回复ID不存在")
     */
    public const ISSUE_COMMENT_REPLY_ID_NOT_EXIST = -11202;

    /**
     * @Message("评论的回复不存在")
     */
    public const ISSUE_COMMENT_REPLY_NOT_EXIST = -11203;

    /**
     * @Message("操作符非法")
     */
    public const ISSUE_OPERATION_INVALID = -11204;

    /**
     * @Message("耗费时间格式不正确")
     */
    public const WORKLOG_SPEND_TIME_INVALID = -11301;

    /**
     * @Message("必须指定剩余时间")
     */
    public const WORKLOG_LEAVE_ESTIMATE_TIME_CANNOT_EMPTY = -11304;

    /**
     * @Message("剩余时间指定格式不正确")
     */
    public const WORKLOG_LEAVE_ESTIMATE_TIME_INVALID = -11305;

    /**
     * @Message("必须制定缩减时间")
     */
    public const WORKLOG_CUT_CANNOT_EMPTY = -11306;

    /**
     * @Message("缩减时间格式不正确")
     */
    public const WORKLOG_CUT_INVALUD = -11307;

    /**
     * @Message("问题不存在")
     */
    public const ISSUE_NOT_FOUND = -11308;

    /**
     * @Message("工作日志不存在或不属于此问题")
     */
    public const WORKLOG_NOT_FOUND = -11309;

    /**
     * @Message("版本名必填")
     */
    public const VERSION_NAME_CANNOT_EMPTY = -11500;

    /**
     * @Message("版本名重复")
     */
    public const VERSION_NAME_REPEATED = -11501;

    /**
     * @Message("版本结束时间必须大于开始时间")
     */
    public const VERSION_END_TIME_MUST_LARGER_THAN_START_TIME = -11502;

    /**
     * @Message("当前版本不存在")
     */
    public const VERSION_NOT_EXIST = -11503;

    /**
     * @Message("版本状态非法")
     */
    public const VERSION_RELEASE_STATUS_INVALID = -11505;

    /**
     * @Message("版本状态必填")
     */
    public const VERSION_RELEASE_STATUS_CANNOT_EMPTY = -11506;

    /**
     * @Message("版本合并的源头不能为空")
     */
    public const VERSION_MERGE_SOURCE_CANNOT_EMPTY = -11507;

    /**
     * @Message("版本合并的源头已被废弃")
     */
    public const VERSION_MERGE_SOURCE_ARCHIVED = -11508;

    /**
     * @Message("版本合并的目标不能为空")
     */
    public const VERSION_MERGE_DEST_CANNOT_EMPTY = -11509;

    /**
     * @Message("版本合并的目标已被废弃")
     */
    public const VERSION_MERGE_DEST_ARCHIVED = -11510;

    /**
     * @Message("版本正在被使用")
     */
    public const VERSION_IS_USED = -11511;

    /**
     * @Message("版本操作码非法")
     */
    public const VERSION_OPERATION_INVALID = -11512;

    /**
     * @Message("看板不存在或不属于此项目")
     */
    public const BOARD_NOT_FOUND = -11601;

    /**
     * @Message("看板类型错误")
     */
    public const BOARD_TYPE_ERROR = -11608;

    /**
     * @Message("报告类型错误")
     */
    public const REPORT_TYPE_ERROR = -11851;

    /**
     * @Message("过滤器名称不能为空")
     */
    public const FILTER_NAME_CANNOT_EMPTY = -11852;

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

    /**
     * @Message("WIKI复制对象不能为空")
     */
    public const WIKI_COPY_OBJECT_NOT_EMPTY = -11960;

    /**
     * @Message("WIKI中dest目录不能为空")
     */
    public const WIKI_DESK_DIT_NOT_EMPTY = -11961;

    /**
     * @Message("WIKI复制的对象不存在")
     */
    public const WIKI_COPY_OBJECT_NOT_EXIST = -11962;

    /**
     * @Message("WIKI中dest目录不存在")
     */
    public const WIKI_DESK_DIR_NOT_EXIST = -11963;

    /**
     * @Message("WIKI中上传文件错误")
     */
    public const WIKI_UPLOAD_FILE_ERRORS = -11959;

    /**
     * @Message("WIKI中对象已被锁定")
     */
    public const WIKI_OBJECT_HAS_BEEN_LOCKED = -11955;

    /**
     * @Message("WIKI中该对象无法解锁")
     */
    public const WIKI_OBJECT_CANNOT_BEEN_UNLOCKED = -11956;

    /**
     * @Message("WIKI中移动的对象不能为空")
     */
    public const WIKI_MOVE_OBJECT_NOT_EMPTY = -11964;

    /**
     * @Message("WIKI中移动的目标目录不能为空")
     */
    public const WIKI_MOVE_DIR_DEST_NOT_EMPTY = -11965;

    /**
     * @Message("WIKI中移动的对象不存在")
     */
    public const WIKI_MOVE_OBJECT_NOT_EXIST = -11966;

    /**
     * @Message("WIKI中移动的目录不存在")
     */
    public const WIKI_MOVE_DIR_NOT_EXIST = -11967;

    /**
     * @Message("类型名称不能重复")
     */
    public const TYPE_NAME_ALREADY_EXIST = -12001;

    /**
     * @Message("类型缩码不能重复")
     */
    public const TYPE_ABB_ALREADY_EXIST = -12003;

    /**
     * @Message("类型不存在或不属于此项目")
     */
    public const TYPE_NOT_FOUND = -12006;

    /**
     * @Message("类型在问题中被使用")
     */
    public const TYPE_USED_ISSUE = -12007;

    /**
     * @Message("工作流不存在")
     */
    public const WORKFLOW_NOT_EXISTS = -12101;

    /**
     * @Message("状态名称不能重复")
     */
    public const STATE_NAME_ALREADY_EXISTS = -12401;

    /**
     * @Message("状态不存在或不属于此项目")
     */
    public const STATE_NOT_FOUND = -12402;

    /**
     * @Message("状态在问题中被使用")
     */
    public const STATE_USED_ISSUE = -12403;

    /**
     * @Message("状态在工作流中被使用")
     */
    public const STATE_USED_WORKFLOW = -12404;

    /**
     * @Message("该状态为系统内置状态，不能编辑和删除")
     */
    public const STATE_IS_SYSTEM = 12405;

    /**
     * @Message("角色非法")
     */
    public const ROLE_INVALID = -12701;

    /**
     * @Message("角色不存在")
     */
    public const ROLE_NOT_EXISTS = -12702;

    /**
     * @Message("角色在项目中被使用")
     */
    public const ROLE_IS_USED = -12703;

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
     * @Message("文件不存在")
     */
    public const AVATAR_ID_NOT_EMPTY = -15100;

    /**
     * @Message("文件删除失败")
     */
    public const FILE_DELETE_FAILD = -15102;

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
     * @Message("标签名称不能重复")
     */
    public const LABEL_NAME_ALREADY_EXISTED = -16102;

    /**
     * @Message("标签不存在或不属于此项目")
     */
    public const LABEL_NOT_FOUND = -16103;

    /**
     * @Message("标签在问题中被使用")
     */
    public const LABEL_USED_IESSUES = -16104;

    /**
     * @Message("文件系统 Domain 设置非法")
     */
    public const FILE_DOMAIN_INVALID = -17000;
}
