<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryService;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Paginator Factory Service Test
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Paul Munson <pmunson@nationalfibre.net>
 */
class PaginatorFactoryServiceTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryService;
     */
    private $service;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManagerMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->service = new PaginatorFactoryService();
    }

    /**
     * Verify that PaginatorFactoryService::createInstance() returns a Paginator object.
     */
    public function testCreateInstance()
    {
        $criteriaMock = $this->createMock('IC\Bundle\Base\ComponentBundle\Entity\Filter\Criteria');

        $criteriaMock
             ->expects($this->once())
             ->method('getQuery');

        $this->assertInstanceOf('Doctrine\ORM\Tools\Pagination\Paginator', $this->service->createInstance($criteriaMock));
    }
}
