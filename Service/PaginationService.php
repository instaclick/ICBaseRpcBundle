<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use IC\Bundle\Base\ComponentBundle\Entity\Filter\RangedCriteriaInterface;
use IC\Bundle\Base\RpcBundle\Service\Filter\Result;

/**
 * Pagination Service.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
class PaginationService
{
    /**
     * @var integer
     */
    private $maximumRecordCount;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryServiceInterface
     */
    private $paginatorFactoryService;

    /**
     * Define the maximum record count
     *
     * @param integer $maximumRecordCount result limit
     */
    public function setMaximumRecordCount($maximumRecordCount)
    {
        $this->maximumRecordCount = (integer) $maximumRecordCount;
    }

    /**
     * Define the paginator factory service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\PaginatorFactoryServiceInterface $paginatorFactoryService
     */
    public function setPaginatorFactoryService(PaginatorFactoryServiceInterface $paginatorFactoryService)
    {
        $this->paginatorFactoryService = $paginatorFactoryService;
    }

    /**
     * Paginate the result set and return a populated result set model
     *
     * @param \IC\Bundle\Base\ComponentBundle\Entity\Filter\Criteria $criteria           filter criteria
     * @param integer                                                $page               page number
     * @param integer                                                $maximumRecordCount result limit
     *
     * @return \IC\Bundle\Base\RpcBundle\Service\Filter\Result
     */
    public function paginate(RangedCriteriaInterface $criteria, $page, $maximumRecordCount)
    {
        $maximumRecordCount = ($maximumRecordCount > 0 || $maximumRecordCount < $this->maximumRecordCount)
            ? $maximumRecordCount
            : $this->maximumRecordCount
        ;

        if ( ! is_int($page) || $page < 1) {
            $page = 1;
        }

        // First result is originally indexed as 0-based
        $criteria->setFirstResult(($page - 1) * $maximumRecordCount);
        $criteria->setMaxResults($maximumRecordCount);

        $paginator  = $this->paginatorFactoryService->createInstance($criteria);
        $count      = $paginator->count();
        $totalPages = ceil($count / $criteria->getMaxResults());

        // Reset the first result if it exceeds our pagination count
        $firstResult = $criteria->getFirstResult() + 1 > $count && $count > 0
            ? ($totalPages - 1) * $maximumRecordCount
            : $criteria->getFirstResult()
        ;
        $criteria->setFirstResult($firstResult);

        // Building filter result
        $filterResult = new Result();

        $filterResult->setTotalPages($totalPages);
        $filterResult->setCurrentPage(ceil($firstResult / $criteria->getMaxResults()) + 1);
        $filterResult->setTotalResults($count);
        $filterResult->setMaxResults($criteria->getMaxResults());
        $filterResult->setFirstResult($firstResult);
        $filterResult->setResultList($count !== 0 ? $paginator->getIterator() : array());

        return $filterResult;
    }
}
