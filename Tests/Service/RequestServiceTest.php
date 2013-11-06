<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RpcBundle\Service\RequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Request Service Test
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 * @author Oleksandr Kovalov <oleksandrk@nationalfibre.net>
 * @author Nick Matantsev <nickm@nationalfibre.net>
 */
class RequestServiceTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\RequestService
     */
    private $service;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializerServiceMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->service               = new RequestService();
        $this->serializerServiceMock = $this->createMock('JMS\Serializer\SerializerInterface');

        $this->service->setSerializerService($this->serializerServiceMock);
    }

    /**
     * Test Deserialize.
     *
     * @param \Symfony\Component\HttpFoundation\Request    $request
     * @param array                                        $deserializedContent
     * @param \Doctrine\Common\Collections\ArrayCollection $expectedCollection
     *
     * @dataProvider validUnserializeRequestDataProvider
     */
    public function testDeserializeRequest(Request $request, $deserializedContent, ArrayCollection $expectedCollection)
    {
        $this->serializerServiceMock
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo($request->getContent()),
                $this->equalTo('Doctrine\Common\Collections\ArrayCollection'),
                $this->equalTo($request->getContentType())
            )
            ->will($this->returnValue($deserializedContent));

        $this->assertEquals($expectedCollection, $this->service->deserializeRequest($request));
    }

    /**
     * Data provider for valid request execution.
     *
     * @return array
     */
    public function validUnserializeRequestDataProvider()
    {
        return array(
            array(
                $this->createRequest('application/json', 'json', '{"foo":"bar"}'),
                array('foo' => 'bar'),
                new ArrayCollection(array('foo' => 'bar')),
            ),
        );
    }

    /**
     * Test Create Response.
     *
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param array                                      $deserializedContent
     * @param string                                     $serializedContent
     *
     * @dataProvider validCreateResponseDataProvider
     */
    public function testCreateResponse(Request $request, $deserializedContent, $serializedContent)
    {
        $this->serializerServiceMock
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->equalTo($deserializedContent),
                $this->equalTo($request->getRequestFormat())
            )
            ->will($this->returnValue($serializedContent));

        $response = $this->service->createResponse($request, $deserializedContent);

        $this->assertEquals($serializedContent, $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Data provider for valid create response execution.
     *
     * @return array
     */
    public function validCreateResponseDataProvider()
    {
        return array(
            array(
                $this->createRequest('application/json', 'json'),
                array('foo' => 'bar'),
                '{"foo":"bar"}',
                'application/json'
            ),
            array(
                $this->createRequest('application/json', 'json'),
                null,
                'null',
                'application/json'
            ),
        );
    }

    /**
     * Create a request object
     *
     * @param string $contentType
     * @param string $requestFormat
     * @param string $content
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function createRequest($contentType, $requestFormat, $content = null)
    {
        $request = Request::create('/foobar', 'POST', array(), array(), array(), array(), $content);

        $request->setRequestFormat($requestFormat);
        $request->headers->set('Content-Type', $contentType);

        return $request;
    }
}
