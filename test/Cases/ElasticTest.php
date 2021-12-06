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

use App\Service\Client\IssueSearch;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ElasticTest extends HttpTestCase
{
    public function testDSL()
    {
        $res = di()->get(IssueSearch::class)->findOrVersion(1, ['resolve_version', 'version2', 'version3']);
        var_dump($res);
        $this->assertIsArray($res);
    }
}
