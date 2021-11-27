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
namespace App\Controller;

use App\Service\Dao\SysSettingDao;
use App\Service\Formatter\SysSettingFormatter;
use Hyperf\Di\Annotation\Inject;

class SysSettingController extends Controller
{
    #[Inject]
    protected SysSettingDao $dao;

    #[Inject]
    protected SysSettingFormatter $formatter;

    /**
     * TODO: rewrite by limx.
     */
    public function show()
    {
        $setting = $this->dao->first();

        return $this->response->success(
            $this->formatter->base($setting)
        );
    }

    /**
     * TODO: rewrite by limx.
     */
    public function update()
    {
    }

    /**
     * TODO: rewrite by limx.
     */
    public function resetPwd()
    {
    }

    /**
     * TODO: rewrite by limx.
     */
    public function sendTestMail()
    {
    }
}
