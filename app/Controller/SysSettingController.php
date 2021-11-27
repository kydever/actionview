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

use App\Request\SendTestMailRequest;
use App\Request\SysSettingSaveRequest;
use App\Service\Dao\SysSettingDao;
use App\Service\Formatter\SysSettingFormatter;
use App\Service\SysSettingService;
use Hyperf\Di\Annotation\Inject;

class SysSettingController extends Controller
{
    #[Inject]
    protected SysSettingDao $dao;

    #[Inject]
    protected SysSettingService $service;

    #[Inject]
    protected SysSettingFormatter $formatter;

    public function show()
    {
        $setting = $this->dao->first();

        return $this->response->success(
            $this->formatter->base($setting)
        );
    }

    public function update(SysSettingSaveRequest $request)
    {
        return $this->response->success(
            $this->service->update($request->all())
        );
    }

    /**
     * TODO: rewrite by limx.
     */
    public function resetPwd()
    {
    }

    public function sendTestMail(SendTestMailRequest $request)
    {
        return $this->response->success(
            $this->service->sendTestMail($request->all())
        );
    }
}
