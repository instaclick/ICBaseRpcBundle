<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use Doctrine\ORM\Query;
use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryService;
use IC\Bundle\Base\ComponentBundle\Entity\Filter\Criteria;
use IC\Bundle\Base\RpcBundle\Service\PaginationService;

/**
 * Paginator Factory Service Test
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Paul Munson <pmunson@nationalfibre.net>
 */
class PaginationServiceTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\ComponentBundle\Entity\Filter\Criteria
     */
    private $criteria;

    /**
     * \Doctrine\ORM\EntityManager
     */
    private $entityManagerMock;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\PaginationService
     */
    private $paginationService;

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $paginatorMock;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryService
     */
    private $paginatorFactoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->paginationService = new PaginationService();
        $this->criteriaMock      = $this->createMock('IC\Bundle\Base\ComponentBundle\Entity\Filter\Criteria');
        $this->entityManagerMock = $this->createMock('Doctrine\ORM\EntityManager');
    }

    /**
     * Test the paginate method with various data sets
     *
     * @param integer $page                         page number
     * @param integer $resultsPerPage               results per page
     * @param integer $totalResults                 total number of records
     * @param integer $expectedPage                 expected page number (in Filter result)
     * @param integer $expectedFirstResult          expected first result index (in Filter result)
     * @param integer $expectedGetIteratorCallCount expected getIterator call count
     *
     * @dataProvider provideDataForPaginateTest
     */
    public function testPaginate($page, $resultsPerPage, $totalResults, $expectedPage, $expectedFirstResult, $expectedGetIteratorCallCount = 1)
    {
        $this->initializePaginatorMock($expectedGetIteratorCallCount);
        $this->initializePaginatorFactoryMock();

        $this->paginationService->setPaginatorFactoryService($this->paginatorFactoryMock);
        $this->paginationService->setMaximumRecordCount(500);

        $this
            ->criteriaMock
            ->expects($this->any())
            ->method('setFirstResult');

        $this
            ->criteriaMock
            ->expects($this->any())
            ->method('getFirstResult')
            ->will($this->returnValue($expectedFirstResult));

        $this
            ->criteriaMock
            ->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo($resultsPerPage));

        $this
            ->criteriaMock
            ->expects($this->any())
            ->method('getMaxResults')
            ->will($this->returnValue($resultsPerPage));

        $this
            ->paginatorMock
            ->expects($this->any())
            ->method('count')
            ->will($this->returnValue($totalResults));

        $result = $this->paginationService->paginate($this->criteriaMock, $page, $resultsPerPage);

        $this->assertEquals(ceil($totalResults/$resultsPerPage), $result->getTotalPages());
        $this->assertEquals($expectedPage, $result->getCurrentPage());
        $this->assertEquals($totalResults, $result->getTotalResults());
        $this->assertEquals($expectedFirstResult, $result->getFirstResult());
        $this->assertEquals($resultsPerPage, $result->getMaxResults());
    }

    /**
     * Initialize a mock PaginatorFactory object
     */
    private function initializePaginatorFactoryMock()
    {
        $this->paginatorFactoryMock = $this->createMock('IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryService');

        $this
            ->paginatorFactoryMock
            ->expects($this->once())
            ->method('createInstance')
            ->will($this->returnValue($this->paginatorMock));
    }

    /**
     * Initialize a mock Paginator object
     *
     * @param integer $count
     */
    private function initializePaginatorMock($count = 1)
    {
        $this->paginatorMock = $this->createMock('Doctrine\ORM\Tools\Pagination\Paginator');

        $this
            ->paginatorMock
            ->expects($this->exactly($count))
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator(array())));
    }

    /**
     * Data provider for paginator test
     *
     * @return array
     */
    public function provideDataForPaginateTest()
    {
        // ( page #, results per page, total results, expected page #, expected first result )
        return array(
            array(1, 25, 100, 1, 0),
            array(2, 25, 100, 2, 25),
            array('sds', 25, 100, 1, 0), // "garbage" (non-integer) page #
            array(-1, 25, 100, 1, 0),    // negative page #
            array(0, 25, 100, 1, 0),     // zero page #
            array(1, 25, 90, 1, 0),      // total results not evenly divisible by # of records per page
            array(42, 25, 90, 4, 75),    // page # exceeds total number of pages
            array(1, 25, 0, 1, 0, 0),    // no records in database (expects zero getIterator calls)
        );
    }
}
