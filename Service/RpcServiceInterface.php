<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

/**
 * RPC Service Interface.
 *
 * To prevent arbitrary services that contain an execute() method from being called,
 * all concrete RPC service classes must implement this marker interface.
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
interface RpcServiceInterface
{
    /* intentionally empty */
}
