<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RPC Controller
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class RpcController extends Controller
{
    /**
     * Execute an RPC action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function executeAction(Request $request)
    {
        return $this->container->get('ic_base_rpc.service.rpc')->execute($request);
    }
}
