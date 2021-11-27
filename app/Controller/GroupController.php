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

use App\Request\GroupSearchRequest;
use App\Request\PaginationRequest;
use App\Service\GroupService;
use Hyperf\Di\Annotation\Inject;

class GroupController extends Controller
{
    #[Inject]
    protected GroupService $service;

    public function index(GroupSearchRequest $request, PaginationRequest $page)
    {
        [$count, $result] = $this->service->index($request->all(), $page->offset(), $page->limit());

        return $this->response->success($result, [
            'options' => [
                'total' => $count,
                'sizePerPage' => $page->limit(),
                'directories' => [],
            ],
        ]);
    }
}
