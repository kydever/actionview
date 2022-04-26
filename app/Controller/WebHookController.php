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

use App\Service\WebHookService;
use Hyperf\Di\Annotation\Inject;

class WebHookController extends Controller
{
    #[Inject]
    protected WebHookService $service;

    public function handle()
    {
        $actions = (array) $this->request->input('actions');
        $email = (string) $this->request->input('email');

        $this->service->handle($email, $actions);

        return $this->response->success(true);
    }
}
