<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\EventListener;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * The request listener to intercept and validate possible RPC requests.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class RequestListener
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface The router
     */
    private $router;

    /**
     * @var array
     */
    private $preferableMimeTypeList = array(
        'application/json',
        'application/xml',
        'text/plain'
    );

    /**
     * Define the routing service.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router Router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Listen for a browser pre-fetch (or pre-render) request
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event Event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ( ! $this->isValidEvent($event)) {
            return;
        }

        if ( ! $this->validateContentType($event)) {
            return;
        }

        if ( ! $this->validateAccept($event)) {
            return;
        }
    }

    /**
     * Check if the current event/request is on the given route.
     *
     * @param GetResponseEvent $event Event
     *
     * @return boolean
     */
    private function isValidEvent(GetResponseEvent $event)
    {
        // Is it a master request (not an ESI request)
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }

        try {
            $routeInfo = $this->router->match($event->getRequest()->getPathInfo());

            return $routeInfo['_route'] == 'ICBaseRpcBundle_Rpc_Execute';
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Validate the request content type.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event Event
     *
     * @return boolean
     */
    private function validateContentType(GetResponseEvent $event)
    {
        $request           = $event->getRequest();
        list($contentType) = explode(';', $request->headers->get('Content-Type'));  // strip off the 'charset=XXX' if present

        if ( ! in_array($contentType, $this->preferableMimeTypeList)) {
            $event->setResponse(
                new Response(sprintf('Unsupported Content-Type [%s] in request.', $contentType), 412)
            );

            return false;
        }

        return true;
    }

    /**
     * Validate the request content type.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event Event
     *
     * @return boolean
     */
    private function validateAccept(GetResponseEvent $event)
    {
        $request      = $event->getRequest();
        $accept       = $request->headers->get('Accept');
        $acceptHeader = AcceptHeader::fromString($accept);

        foreach ($this->preferableMimeTypeList as $preferableMimeType) {
            if ($acceptHeader->has($preferableMimeType) && $request->getFormat($preferableMimeType)) {
                $request->setRequestFormat($request->getFormat($preferableMimeType));

                return true;
            }
        }

        $event->setResponse(
            new Response(sprintf('Unsupported Accept [%s] in request.', $accept), 412)
        );

        return false;
    }
}
