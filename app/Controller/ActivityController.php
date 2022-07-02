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

use App\Service\ActivityService;
use Hyperf\Di\Annotation\Inject;

class ActivityController extends Controller
{
    #[Inject]
    protected ActivityService $service;

    public function index()
    {
        $key = get_project_key();
        $input = $this->request->all();

        $result = $this->service->index($key, $input);

        return $this->response->success($result, [
            'options' => [
                'current_time' => time(),
            ],
        ]);
    }
}
