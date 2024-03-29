<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Service;

use IC\Bundle\Base\SecurityBundle\Resource\SecuredResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * Executor Service.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class ExecutorService
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\ModelFactoryService
     */
    private $modelFactoryService;

    /**
     * Define the container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Define the normalizer service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\ModelFactoryService $modelFactoryService
     */
    public function setModelFactoryService(ModelFactoryService $modelFactoryService)
    {
        $this->modelFactoryService = $modelFactoryService;
    }

    /**
     * Executes service with given arguments
     *
     * @param string $serviceId    Service id
     * @param array  $argumentList List of arguments with values
     *
     * @throws \Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException
     * @throws \Exception
     *
     * @return mixed
     */
    public function execute($serviceId, array $argumentList)
    {
        $service = $this->findService($serviceId);

        if ( ! $this->isGranted($service)) {
            throw new InsufficientAuthenticationException();
        }

        return $this->executeUsingModel($service, $argumentList);
    }

    /**
     * Find the endpoint RPC service.
     *
     * @param string $serviceId
     *
     * @throws \InvalidArgumentException
     *
     * @return object
     */
    private function findService($serviceId)
    {
        if ( ! $serviceId
            || ! $this->container->has($serviceId)
            || ! ($service = $this->container->get($serviceId)) instanceof RpcServiceInterface
        ) {
            throw new \InvalidArgumentException(sprintf('Invalid service [%s]', $serviceId));
        }

        return $service;
    }

    /**
     * Validate if the service is granted.
     *
     * @param object $service
     *
     * @return boolean
     */
    private function isGranted($service)
    {
        if ( ! $service instanceof SecuredResourceInterface) {
            return true;
        }

        $permission           = $service->getPermission('execute');
        $authorizationService = $this->container->get('ic_base_security.service.authorization');

        return $authorizationService->isGranted($permission->getMask(), $permission->getResourceName());
    }

    /**
     * Execute service using model as parameter.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface $service
     * @param array                                                 $argumentList
     *
     * @return array
     */
    private function executeUsingModel(RpcServiceInterface $service, array $argumentList)
    {
        $reflection = new \ReflectionMethod($service, 'execute');
        $modelClass = $service->getModel();
        $model      = $this->modelFactoryService->createModel($modelClass, $argumentList);

        if ($model === null) {
            return new Response('Invalid arguments.', 400);
        }

        return $reflection->invokeArgs($service, array($model));
    }
}
