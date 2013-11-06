<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service;

use IC\Bundle\Base\SecurityBundle\Resource\Permission;
use IC\Bundle\Base\SecurityBundle\Resource\SecuredResourceInterface;

/**
 * Rpc Mock Secured Service.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class MockSecuredService extends MockService implements SecuredResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermission($action = null)
    {
        return new Permission('ic_base_rpc.service.mock_secured.execute');
    }
}
