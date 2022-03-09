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

use App\Service\Formatter\TypeFormatter;
use App\Service\TypeService;
use Hyperf\Di\Annotation\Inject;

class TypeController extends Controller
{
    #[Inject]
    protected TypeService $service;

    #[Inject]
    protected TypeFormatter $formatter;

    public function index()
    {
        [$list, $options] = $this->service->findByProject(get_project());

        return $this->response->success(
            $list,
            [
                'options' => $options,
            ]
        );
    }
}
