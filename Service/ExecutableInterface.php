<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

/**
 * Executable Interface
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
interface ExecutableInterface
{
    /**
     * Execute
     *
     * @param object $model
     *
     * @return mixed
     */
    public function execute($model);
}
