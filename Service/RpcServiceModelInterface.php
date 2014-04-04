<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

/**
 * RPC Service Model Interface.
 *
 * Implemented by services that are using a model to filter and validate external input.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
interface RpcServiceModelInterface
{
    /**
     * Retrieve model class name, i.e., \IC\Foo\Bar\BazModel
     *
     * @return string
     */
    public function getModel();
}
