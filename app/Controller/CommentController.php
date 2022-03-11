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

use App\Request\CommentStoreRequest;
use App\Service\CommentService;
use Hyperf\Di\Annotation\Inject;

class CommentController extends Controller
{
    #[Inject]
    protected CommentService $service;

    public function index(int $id)
    {
        $isAsc = $this->request->input('sort') === 'asc';

        $result = $this->service->index($id, $isAsc);

        return $this->response->success($result, [
            'options' => [
                'current_time' => time(),
            ],
        ]);
    }

    public function store(int $id, CommentStoreRequest $request)
    {
        $user = get_user();
        $project = get_project();

        $result = $this->service->store($id, $user, $project, $request->all());

        return $this->response->success($result);
    }
}
