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
namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class WebHookTest extends HttpTestCase
{
    public function testWorkflowHandle()
    {
        $this->markTestSkipped('不使用这种 Hook 方式');

        $this->json('/workflow/handle', [
            'actions' => [
                ['action' => 'Resolved', 'project' => 'p_ceshi', 'no' => '2'],
            ],
        ]);

        $this->assertTrue(true);
    }
}
