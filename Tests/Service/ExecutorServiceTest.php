<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use IC\Bundle\Base\RpcBundle\Service\ExecutorService;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockModelService;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockSecuredService;
use IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockService;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Executor Service Test
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Paul Munson <pmunson@nationalfibre.net>
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class ExecutorServiceTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\ExecutorService
     */
    private $service;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $containerMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->service                 = new ExecutorService();
        $this->containerMock           = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->authorizationService    = $this->createMock('IC\Bundle\Base\SecurityBundle\Service\AuthorizationService');
        $this->modelFactoryServiceMock = $this->createMock('IC\Bundle\Base\RpcBundle\Service\ModelFactoryService');

        $this->service->setContainer($this->containerMock);
        $this->service->setModelFactoryService($this->modelFactoryServiceMock);
    }

    /**
     * Test Execute.
     *
     * @param string $serviceId
     * @param array  $requestData
     * @param array  $expectedParameterList
     *
     * @dataProvider validDataProvider
     */
    public function testExecuteUsingRawParameterList($serviceId, $requestData, $expectedParameterList)
    {
        $this->containerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockService()));

        $result = $this->service->execute($serviceId, $requestData);

        $this->assertEquals($expectedParameterList, $result);
    }

    /**
     * Data provider for valid execution.
     *
     * @return array
     */
    public function validDataProvider()
    {
        return array(
            array(
                'service.id',
                array(
                    'expectedParameter1' => 'foo',
                    'expectedParameter2' => 'bar'
                ),
                array(
                    0 => 'foo',
                    1 => 'bar',
                    2 => 'default'
                ),
            ),
            array(
                'service.id',
                array(
                    'expectedParameter2' => 'bar',
                    'expectedParameter1' => 'foo'
                ),
                array(
                    0 => 'foo',
                    1 => 'bar',
                    2 => 'default'
                ),
            ),
        );
    }

    /**
     * Test execute using model.
     */
    public function testExecuteUsingModel()
    {
        $serviceId   = 'service.id';
        $requestData = array();
        $model       = new MockEntity('foo', 845, null);

        $this->containerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockModelService()));

        $this->modelFactoryServiceMock
            ->expects($this->once())
            ->method('createModel')
            ->with(
                'IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity',
                $requestData
            )
            ->will($this->returnValue($model));

        $result = $this->service->execute($serviceId, $requestData);

        $this->assertEquals($model, $result);
    }

    /**
     * Test execute using model results in bad method response if unable to build model.
     */
    public function testExecuteUsingModelResultsInBadMethodResponseIfUnableToBuildModel()
    {
        $serviceId   = 'service.id';
        $requestData = array();
        $model       = new MockEntity('foo', 845, null);

        $this->containerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockModelService()));

        $this->modelFactoryServiceMock
            ->expects($this->once())
            ->method('createModel')
            ->with(
                'IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity',
                $requestData
            )
            ->will($this->returnValue(null));

        $result = $this->service->execute($serviceId, $requestData);

        $this->assertEquals(400, $result->getStatusCode());
    }

    /**
     * Test that RpcExecutorService executes the given service.
     *
     * @param string $serviceId
     * @param array  $requestData
     *
     * @dataProvider invalidMethodSignatureDataProvider
     *
     * @expectedException \BadMethodCallException
     */
    public function testExecuteThrowsBadMethodCallExceptionWithWrongMethodSignature($serviceId, $requestData)
    {
        $this->containerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockService()));

        $result = $this->service->execute($serviceId, $requestData);
    }

    /**
     * Data provider for valid execution.
     *
     * @return array
     */
    public function invalidMethodSignatureDataProvider()
    {
        return array(
            array(
                'service.id',
                array(),
            ),
            array(
                'service.id',
                array(
                    'expectedParameter1' => 'foo',
                ),
            ),
            array(
                'service.id',
                array(
                    'expectedParameter2' => 'bar',
                ),
            ),
            array(
                'service.id',
                array(
                    'expectedParameter1' => 'bar',
                    'expectedParameter3' => 'bar',
                ),
            ),
        );
    }

    /**
     * Test Execute with an a service id that does not exist in the container.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteThrowsInvalidArgumentExceptionWithServiceNotFound()
    {
        $serviceId = 'service.id';

        $this->containerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(false));

        $this->service->execute($serviceId, array());
    }

    /**
     * Test Execute with an invalid service id.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteThrowsInvalidArgumentExceptionWithInvalidServiceId()
    {
        $this->service->execute(null, array());
    }

    /**
     * Test Execute SecuredResource that grants access.
     */
    public function testExecuteSecuredResourceThatGrantsAccess()
    {
        $serviceId = 'service.id';

        $this->containerMock
            ->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockSecuredService()));

        $this->authorizationService
             ->expects($this->once())
             ->method('isGranted')
             ->with($this->equalTo('CONSUME'), $this->equalTo('ic_base_rpc.service.mock_secured.execute'))
             ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('ic_base_security.service.authorization'))
            ->will($this->returnValue($this->authorizationService));

        $this->service->execute($serviceId, array('expectedParameter1' => 'foo', 'expectedParameter2' => 'bar'));
    }

    /**
     * Test Execute SecuredResource that denies access.
     *
     * @expectedException Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException
     */
    public function testExecuteSecuredResourceThatDeniesAccess()
    {
        $serviceId = 'service.id';

        $this->containerMock
            ->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $this->containerMock
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(new MockSecuredService()));

        $this->authorizationService
             ->expects($this->once())
             ->method('isGranted')
             ->will($this->returnValue(false));

        $this->containerMock
            ->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('ic_base_security.service.authorization'))
            ->will($this->returnValue($this->authorizationService));

        $this->service->execute($serviceId, array());
    }
}
