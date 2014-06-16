<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Rpc\Service;

use IC\Bundle\Base\RpcBundle\Service\ExecutableInterface;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;

/**
 * Anonymous Rpc service filter|validates parameters through a model before delegating the execution
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class AnonymousRpcService implements RpcServiceInterface
{
    /**
     * @var object
     */
    private $model;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\ExecutableInterface
     */
    private $service;

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
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
     * Define the executable Service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\ExecutableInterface $service
     */
    public function setService(ExecutableInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Execute leadService
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
