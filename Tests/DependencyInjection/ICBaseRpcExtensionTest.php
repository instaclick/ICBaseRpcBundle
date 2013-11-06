<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\DependencyInjection;

use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;
use IC\Bundle\Base\RpcBundle\DependencyInjection\ICBaseRpcExtension;

/**
 * Test for ICBaseRpcExtension
 *
 * @group ICBaseRpcBundle
 * @group Unit
 * @group DependencyInjection
 *
 * @author Oleksii Strutsynskyi <oleksiis@nationalfibre.net>
 */
class ICBaseRpcExtensionTest extends ExtensionTestCase
{
    /**
     * Test configuration
     */
    public function testConfiguration()
    {
        $loader = new ICBaseRpcExtension();

        $this->load($loader, array());
    }
}
