<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service;

use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceModelInterface;

/**
 * Mock model service.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class MockModelService implements RpcServiceInterface, RpcServiceModelInterface
{
    /**
     * Execute method.
     *
     * @param object $model
     *
     * @return array
     */
    public function execute($model)
    {
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return 'IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity';
    }
}
