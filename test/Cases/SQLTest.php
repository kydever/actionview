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

use App\Service\Dao\AccessProjectLogDao;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\IssueDao;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class SQLTest extends HttpTestCase
{
    public function testJsonContains()
    {
        $res = di()->get(AclGroupDao::class)->findByUserId(1);

        $this->assertInstanceOf(Collection::class, $res);
    }

    public function testFindLatestProjectKeys()
    {
        $res = di()->get(AccessProjectLogDao::class)->findLatestProjectKeys(1);

        $this->assertIsArray($res);
    }

    public function testQuery()
    {
        $query = di()->get(IssueDao::class)->getQuery([]);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function testCountGroupBy()
    {
        $res = di()->get(IssueDao::class)->countGroupByProjectKeys(['test', 'test2']);
        $this->assertIsArray($res);

        $res = di()->get(IssueDao::class)->countGroupByProjectKeys(['test', 'test2'], '', 1);
        $this->assertIsArray($res);
    }
}
