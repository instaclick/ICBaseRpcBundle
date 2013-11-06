<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Paginator Factory Service.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
class PaginatorFactoryService implements PaginatorFactoryServiceInterface
{
    /**
     * Create paginator instance.
     *
     * @param \IC\Bundle\Base\ComponentBundle\Entity\Filter\CriteriaInterface $criteria
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function createInstance($criteria)
    {
        return new Paginator($criteria->getQuery());
    }
}
