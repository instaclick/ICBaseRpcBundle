<?php
/**
 * @copyright 2012 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RpcBundle\Service\RpcService;

/**
 * Rpc Service Test
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class RpcServiceTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RpcService
     */
    private $service;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RpcExecutorService
     */
    private $executorService;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RequestService
     */
    private $requestService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->service         = new RpcService();
        $this->executorService = $this->createMock('IC\Bundle\Base\RpcBundle\Service\ExecutorService');
        $this->requestService  = $this->createMock('IC\Bundle\Base\RpcBundle\Service\RequestService');

        $this->service->setExecutorService($this->executorService);
        $this->service->setRequestService($this->requestService);
    }

    /**
     * Test Execute.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $requestData
     * @param string                                       $expectedServiceId
     * @param string                                       $expectedArgumentList
     * @param mixed                                        $expectedContent
     *
     * @dataProvider validRequestProvider
     */
    public function testExecute($requestData, $expectedServiceId, $expectedArgumentList, $expectedContent)
    {
        $request  = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');

        $this->requestService
            ->expects($this->once())
            ->method('deserializeRequest')
            ->with($this->equalTo($request))
            ->will($this->returnValue($requestData));

        $this->requestService
            ->expects($this->once())
            ->method('createResponse')
            ->with($this->equalTo($request), $this->equalTo($expectedContent))
            ->will($this->returnValue($response));

        $this->executorService
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($expectedServiceId), $this->equalTo($expectedArgumentList))
            ->will($this->returnValue($expectedContent));

        $this->assertEquals($response, $this->service->execute($request));
    }

    /**
     * Data provider for valid request.
     *
     * @return array
     */
    public function validRequestProvider()
    {
        return array(
            array(
                new ArrayCollection(array(
                    'service'   => 'ic_base_foobar.rpc.service.foobar',
                    'arguments' => array('foo' => 'bar')
                )),
                'ic_base_foobar.rpc.service.foobar',
                array('foo' => 'bar'),
                "foobar"
            ),
            array(
                new ArrayCollection(array(
                    'service'   => 'ic_base_foobar.rpc.service.foobar',
                    'arguments' => array()
                )),
                'ic_base_foobar.rpc.service.foobar',
                array(),
                "foobar"
            ),
            array(
                new ArrayCollection(array(
                    'service'   => 'ic_base_foobar.rpc.service.foobar',
                    'arguments' => null
                )),
                'ic_base_foobar.rpc.service.foobar',
                array(),
                "foobar"
            ),
            array(
                new ArrayCollection(array(
                    'service'   => 'ic_base_foobar.rpc.service.foobar'
                )),
                'ic_base_foobar.rpc.service.foobar',
                array(),
                "foobar"
            ),
        );
    }
}
