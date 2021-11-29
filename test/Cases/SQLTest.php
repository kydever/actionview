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

use App\Constants\Permission;
use App\Constants\ProjectConstant;
use App\Service\Dao\AccessProjectLogDao;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRolePermissionDao;
use App\Service\Dao\IssueDao;
use App\Service\Dao\UserDao;
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

        $res = di()->get(AclRolePermissionDao::class)->findProjectPermissions(Permission::ISSUE_ASSIGNED, ProjectConstant::SYS);

        $this->assertInstanceOf(Collection::class, $res);
        $this->assertTrue($res->isNotEmpty());
    }

    public function testORMJsonContainsRelation()
    {
        $model = di()->get(UserDao::class)->first(1);
        $this->assertInstanceOf(Collection::class, $model->groups);

        $model = di()->get(UserDao::class)->findMany([1]);
        $model->load('groups');
        $this->assertInstanceOf(Collection::class, $model[0]->groups);
    }

    public function testORMInJsonArrayRelation()
    {
        $group = di()->get(AclGroupDao::class)->first(1);
        if ($group === null) {
            $this->markTestSkipped('没有对应的用户组');
        }

        $res = $group->userModels;
        $this->assertInstanceOf(Collection::class, $res);

        $group = di()->get(AclGroupDao::class)->first(1);
        $this->assertFalse($group->relationLoaded('userModels'));
        $group->load('userModels');
        $this->assertTrue($group->relationLoaded('userModels'));
        $this->assertInstanceOf(Collection::class, $group->userModels);
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
