<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Rpc\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pagination model
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class PaginationModel
{
    /**
     * @Assert\Type(type="integer")
     *
     * @var integer
     */
    private $page;

    /**
     * @Assert\Type(type="integer")
     *
     * @var integer
     */
    private $maximumRecordCount;

    /**
     * @param integer $maximumRecordCount
     */
    public function setMaximumRecordCount($maximumRecordCount)
    {
        $this->maximumRecordCount = $maximumRecordCount;
    }

    /**
     * @return integer
     */
    public function getMaximumRecordCount()
    {
        return $this->maximumRecordCount;
    }

    /**
     * @param integer $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }
}
