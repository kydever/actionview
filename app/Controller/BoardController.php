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

use App\Service\BoardService;
use App\Service\Formatter\BoardFormatter;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class BoardController extends Controller
{
    #[Inject]
    protected BoardService $service;

    #[Inject]
    protected BoardFormatter $formatter;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        [$result, $options] = $this->service->index($user, $project);

        return $this->response->success([
            $result,
            [
                'options' => $options,
            ],
        ]);
    }
}
