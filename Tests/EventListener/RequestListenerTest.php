<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\EventListener;

use IC\Bundle\Base\RpcBundle\EventListener\RequestListener;
use IC\Bundle\Base\TestBundle\Test\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Request Listener Test
 *
 * @group ICBaseRpc
 * @group Listener
 * @group Unit
 *
 * @author Paul Munson <pmunson@nationalfibre.net>
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class RequestListenerTest extends TestCase
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $routerMock;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var \IC\Bundle\Base\RpcBundle\EventListener\RequestListener
     */
    private $listener;

    /**
     * @var array
     */
    private $validRoute = array('_route' => 'ICBaseRpcBundle_Rpc_Execute');

    /**
     * @var array
     */
    private $invalidRoute = array('_route' => 'ICFooBarBundle_Foo_Bar');

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->listener   = new RequestListener();
        $this->routerMock = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $this->kernelMock = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $this->listener->setRouter($this->routerMock);
    }

    /**
     * Test the onKernelRequest throws
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $contentType
     * @param string                                    $expectedRequestFormat
     *
     * @dataProvider validRequestProvider
     */
    public function testOnKernelRequest($request, $contentType, $expectedRequestFormat)
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $request->headers->set('Content-Type', $contentType);

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/'))
            ->will($this->returnValue($this->validRoute));

        $this->listener->onKernelRequest($event);

        $this->assertEquals($expectedRequestFormat, $request->getRequestFormat());
        $this->assertTrue($event->hasResponse(), 'Should have an error response.');
        $this->assertEquals($event->getResponse(), $response);
    }

    /**
     * Data provider for a valid request
     *
     * @return array
     */
    public function validRequestProvider()
    {
        return array(
            array(
                Request::create('/', 'POST', array(), array(), array(), array('HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')),
                'application/xml',
                'xml'
            ),
            array(
                Request::create('/', 'POST', array(), array(), array(), array('HTTP_ACCEPT' => 'application/json;q=0.8')),
                'application/xml',
                'json'
            ),
            array(
                Request::create('/', 'POST', array(), array(), array(), array('HTTP_ACCEPT' => 'application/json')),
                'application/xml',
                'json'
            ),
        );
    }

    /**
     * Test the onKernelRequest method
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $requestType
     *
     * @dataProvider invalidEventDataProvider
     */
    public function testOnKernelRequestIsValidEvent($request, $requestType)
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, $request, $requestType, $response);

        $this->listener->onKernelRequest($event);

        // We should still have an unmodified response
        $this->assertTrue($event->hasResponse());
        $this->assertEquals($response, $event->getResponse());
    }

    /**
     * Data provider for invalid request type
     *
     * @return array
     */
    public function invalidEventDataProvider()
    {
        return array(
            array(
                Request::create('/', 'GET'), HttpKernelInterface::MASTER_REQUEST,
            ),
            array(
                Request::create('/', 'POST'), HttpKernelInterface::SUB_REQUEST
            ),
        );
    }

    /**
     * Test if an invalid route service threw an exception it is handled
     */
    public function testOnKernelRequestIsValidEventCatchesRouteException()
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, Request::create('/', 'POST'), HttpKernelInterface::MASTER_REQUEST, $response);

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/'))
            ->will($this->throwException(new \Exception()));

        $this->listener->onKernelRequest($event);

        // We should still have an unmodified response
        $this->assertEquals($response, $event->getResponse());
    }

    /**
     * Test when a non-rpc route is requested the event listener does nothing to the response
     */
    public function testOnKernelRequestIsValidEventWithNonRpcRouteMatch()
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, Request::create('/', 'POST'), HttpKernelInterface::MASTER_REQUEST, $response);

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/'))
            ->will($this->returnValue($this->invalidRoute));

        $this->listener->onKernelRequest($event);

        // We should still have an unmodified response
        $this->assertTrue($event->hasResponse());
        $this->assertEquals($response, $event->getResponse());
    }

    /**
     * Test when an rpc route is requested with invalid content type a 412 response is returned
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $contentType
     *
     * @dataProvider invalidContentTypeRequestProvider
     */
    public function testOnKernelRequestWithInvalidContentTypeReturns412Response($request, $contentType)
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, $request, HttpKernelInterface::MASTER_REQUEST);

        $request->headers->set('Content-Type', $contentType);

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/'))
            ->will($this->returnValue($this->validRoute));

        $this->listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse(), 'Should have an error response.');
        $this->assertEquals($event->getResponse()->getStatusCode(), 412);
        $this->assertEquals($event->getResponse()->getContent(), sprintf('Unsupported Content-Type [%s] in request.', $contentType));
    }

    /**
     * Data provider for requests with invalid content types
     *
     * @return array
     */
    public function invalidContentTypeRequestProvider()
    {
        return array(
            array(
                Request::create('/', 'POST'), ''
            ),
            array(
                Request::create('/', 'POST'), 'text/plain'
            ),
            array(
                Request::create('/', 'POST'), 'application/foobar'
            ),
        );
    }

    /**
     * Test the onKernelRequest throws
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $contentType
     * @param string                                    $expectedAccept
     *
     * @dataProvider invalidAcceptRequestProvider
     */
    public function testOnKernelRequestWithInvalidAcceptReturns412Response($request, $contentType, $expectedAccept)
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $event    = $this->createEvent($this->kernelMock, $request, HttpKernelInterface::MASTER_REQUEST);

        $request->headers->set('Content-Type', $contentType);

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/'))
            ->will($this->returnValue($this->validRoute));

        $this->listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse(), 'Should have an error response.');
        $this->assertEquals($event->getResponse()->getStatusCode(), 412);
        $this->assertEquals($event->getResponse()->getContent(), sprintf('Unsupported Accept [%s] in request.', $expectedAccept));
    }

    /**
     * Data provider for requests with invalid accept headers
     *
     * @return array
     */
    public function invalidAcceptRequestProvider()
    {
        return array(
            array(
                Request::create('/', 'POST', array(), array(), array(), array('HTTP_ACCEPT' => '')),
                'application/json',
                '',
            ),
            array(
                Request::create('/', 'POST', array(), array(), array(), array('HTTP_ACCEPT' => 'application/foobar')),
                'application/json',
                'application/foobar'
            ),
        );
    }

    /**
     * Create the event
     *
     * @param Symfony\Component\HttpKernel\HttpKernelInterface $kernelMock
     * @param Symfony\Component\HttpFoundation\Request         $request
     * @param integer                                          $requestType
     * @param \Symfony\Component\HttpFoundation\Response       $response
     *
     * @return \Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    private function createEvent(HttpKernelInterface $kernelMock, Request $request, $requestType, Response $response = null)
    {
        $event = new GetResponseEvent($kernelMock, $request, $requestType);

        if ($response) {
            $event->setResponse($response);
        }

        return $event;
    }
}
