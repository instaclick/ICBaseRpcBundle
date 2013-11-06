<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

/**
 * Paginator Factory Service Interface.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
interface PaginatorFactoryServiceInterface
{
    /**
     * Create paginator instance.
     *
     * @param mixed $criteria
     */
    public function createInstance($criteria);
}
