<?php
/**
 * @copyright 2014 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use IC\Bundle\Base\RpcBundle\Service\DeepFilterService;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockNestedEntity;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Deep filter service test.
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class DeepFilterServiceTest extends TestCase
{
    /**
     * Test filter entity.
     */
    public function testFilterEntity()
    {
        $nestedEntity = new MockNestedEntity('baz', 907);
        $entity       = new MockEntity('foo', 845, $nestedEntity);

        $filterServiceMock = $this->createMock('\DMS\Bundle\FilterBundle\Service\Filter');

        $filterServiceMock
            ->expects($this->at(0))
            ->method('filterEntity')
            ->with($nestedEntity);

        $filterServiceMock
            ->expects($this->at(1))
            ->method('filterEntity')
            ->with($entity);

        $deepFilterService = new DeepFilterService();

        $deepFilterService->setFilterService($filterServiceMock);
        $deepFilterService->filterEntity($entity);
    }

    /**
     * Test filter entity with null object.
     */
    public function testFilterEntityWithNullObject()
    {
        $filterServiceMock = $this->createMock('\DMS\Bundle\FilterBundle\Service\Filter');

        $filterServiceMock
            ->expects($this->never())
            ->method('filterEntity');

        $deepFilterService = new DeepFilterService();

        $deepFilterService->setFilterService($filterServiceMock);
        $deepFilterService->filterEntity(null);
    }
}
