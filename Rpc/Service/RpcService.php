<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Rpc\Service;

use IC\Bundle\Base\RpcBundle\Service\ExecutableInterface;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceModelInterface;
use IC\Bundle\Base\SecurityBundle\Resource\Permission;
use IC\Bundle\Base\SecurityBundle\Resource\SecuredResourceInterface;

/**
 * Rpc Service
 *
 * This RPC service filter|validates parameters through a model and checks the permission before delegating the execution
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class RpcService implements RpcServiceInterface, RpcServiceModelInterface, SecuredResourceInterface
{
    /**
     * @var object
     */
    private $model;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface
     */
    private $service;

    /**
     * @var string
     */
    private $permission;

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\ExecutableInterface $service
     */
    public function setService(ExecutableInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Define the model.
     *
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission($action = null)
    {
        return new Permission($this->permission);
    }

    /**
     * {@inheritdoc}
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * Execute service
     *
     * @param object $model
     *
     * @return string
     */
    public function execute($model)
    {
        return $this->service->execute($model);
    }
}
