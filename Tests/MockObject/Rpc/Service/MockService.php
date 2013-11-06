<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service;

use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;

/**
 * Rpc Mock Service.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class MockService implements RpcServiceInterface
{
    /**
     * Execute method.
     *
     * @param mixed $expectedParameter1
     * @param mixed $expectedParameter2
     *
     * @return array
     */
    public function execute($expectedParameter1, $expectedParameter2)
    {
        return func_get_args();
    }
}
