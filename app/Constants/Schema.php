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

class Schema
{
    public const ASSIGNEE = 'assignee';

    public const RESOLUTION = 'resolution';

    public const PRIORITY = 'priority';

    public const MODULE = 'module';

    public const EPIC = 'epic';

    public const LABELS = 'labels';

    public const TYPE = 'type';

    public const DEFAULT_ISSUE_KEYS = [
        self::TYPE,
        self::RESOLUTION,
    ];

    public const FIELD_TIME_TRACKING = 'TimeTracking';

    public const FIELD_DATE_PICKER = 'DatePicker';

    public const FIELD_DATE_TIME_PICKER = 'DateTimePicker';

    public const FIELD_SINGLE_USER = 'SingleUser';

    public const FIELD_MULTI_USER = 'MultiUser';

    public const FIELD_FILE = 'File';

    public const FIELD_SELECT = 'Select';

    public const FIELD_SINGLE_VERSION = 'SingleVersion';

    public const FIELD_RADIO_GROUP = 'RadioGroup';

    public const FIELD_MULTI_SELECT = 'MultiSelect';

    public const FIELD_MULTI_VERSION = 'MultiVersion';

    public const FIELD_CHECKBOX_GROUP = 'CheckboxGroup';

    public const FIELD_DURATION = 'Duration';

    public const FIELD_TEXT = 'Text';

    public const FIELD_TEXT_AREA = 'TextArea';

    public const FIELD_RICH_TEXT_EDITOR = 'RichTextEditor';

    public const FIELD_URL = 'Url';

    public const FIELD_NUMBER = 'Number';

    public const FIELD_INTEGER = 'Integer';
}
