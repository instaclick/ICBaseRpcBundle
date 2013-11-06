<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Service;

use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;
use IC\Bundle\Base\SecurityBundle\Resource\SecuredResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * Define the container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Executes service with given arguments
     *
     * @param string $serviceId    Service id
     * @param array  $argumentList List of arguments with values
     *
     * @return mixed
     */
    public function execute($serviceId, array $argumentList)
    {
        $service = $this->findService($serviceId);

        if ( ! $this->isGranted($service)) {
            throw new InsufficientAuthenticationException();
        }

        $reflection    = new \ReflectionMethod($service, 'execute');
        $parameterList = array();

        foreach ($reflection->getParameters() as $parameter) {
            if (isset($argumentList[$parameter->getName()])) {
                $parameterList[] = $argumentList[$parameter->getName()];
                continue;
            }

            if ( ! $parameter->isOptional()) {
                throw new \BadMethodCallException(
                    sprintf('Missing parameter [%s] to execute service [%s]', $parameter, get_class($service))
                );
            }

            $parameterList[] = $parameter->getDefaultValue();
        }

        return $reflection->invokeArgs($service, $parameterList);
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
}
