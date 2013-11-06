<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * Rpc Service.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class RpcService
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\ExecutorService
     */
    private $executorService;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RequestService
     */
    private $requestService;

    /**
     * Define the executor service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\ExecutorService $executorService
     */
    public function setExecutorService(ExecutorService $executorService)
    {
        $this->executorService = $executorService;
    }

    /**
     * Define the request service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\RequestService $requestService
     */
    public function setRequestService(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * Execute the endpoint service.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute(Request $request)
    {
        $requestData     = $this->requestService->deserializeRequest($request);
        $responseContent = $this->executorService->execute($requestData->get('service'), (array) $requestData->get('arguments'));

        return $this->requestService->createResponse($request, $responseContent);
    }
}
