<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Service;

use DMS\Bundle\FilterBundle\Service\Filter;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface;
use IC\Bundle\Base\RpcBundle\Service\RpcServiceModelInterface;
use IC\Bundle\Base\SecurityBundle\Resource\SecuredResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Validator\Validator;

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
     * @var \IC\Bundle\Base\RpcBundle\Service\NormalizerService
     */
    private $normalizerService;

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
     * @param \IC\Bundle\Base\RpcBundle\Service\NormalizerService $normalizerService
     */
    public function setNormalizerService(NormalizerService $normalizerService)
    {
        $this->normalizerService = $normalizerService;
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

        if ($service instanceof RpcServiceModelInterface) {
            return $this->executeUsingModel($service, $argumentList);
        }

        // TODO: Remove this option after all RPC services use the new Model option.
        return $this->executeUsingRawParameterList($service, $argumentList);
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
     * @param \IC\Bundle\Base\RpcBundle\Service\RpcServiceModelInterface $service
     * @param array                                                      $argumentList
     *
     * @return array
     */
    private function executeUsingModel(RpcServiceModelInterface $service, array $argumentList)
    {
        $reflection = new \ReflectionMethod($service, 'execute');
        $modelClass = $service->getModel();
        $model      = $this->normalizerService->toModel($modelClass, $argumentList);

        if ($model === null) {
            return new Response('Invalid arguments.', 400);
        }

        return $reflection->invokeArgs($service, array($model));
    }

    /**
     * Execute service using raw parameters.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\RpcServiceInterface $service
     * @param array                                                 $argumentList
     *
     * @return mixed
     */
    private function executeUsingRawParameterList($service, array $argumentList)
    {
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
}
