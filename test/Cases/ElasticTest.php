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

use App\Constants\ProjectConstant;
use App\Service\Client\IssueSearch;
use App\Service\ProviderService;
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
        $this->assertIsArray($res);
    }

    public function testGetIssueMapping()
    {
        $fields = di()->get(ProviderService::class)->getFieldList(ProjectConstant::SYS);
        $fields = $fields->columns(['key', 'type'])->toArray();

        $this->assertTrue(true);
    }
}
