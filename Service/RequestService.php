<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request Service.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 * @author Nick Matantsev <nickm@nationalfibre.net>
 */
class RequestService
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializerService;

    /**
     * Define the serializer.
     *
     * @param \JMS\Serializer\SerializerInterface $serializerService
     */
    public function setSerializerService(SerializerInterface $serializerService)
    {
        $this->serializerService = $serializerService;
    }

    /**
     * Create the request data.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return 'Doctrine\Common\Collections\ArrayCollection'
     */
    public function deserializeRequest(Request $request)
    {
        $content = $request->getContent();
        $format  = $request->getContentType();
        $type    = 'Doctrine\Common\Collections\ArrayCollection';

        return new ArrayCollection(
            (array) $this->serializerService->deserialize($content, $type, $format)
        );
    }

    /**
     * Create the serialized response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed                                     $content
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(Request $request, $content)
    {
        // NOTE: even null values get serialized appropriately

        $context = new SerializationContext();
        $context->setAttribute('translatable', true);
        $context->setSerializeNull(true);

        $content = $this->serializerService->serialize($content, $request->getRequestFormat('json'), $context);

        return new Response(
            $content,
            200,
            array(
                'Content-type'   => $request->getMimeType($request->getRequestFormat('json')),
                'Content-length' => strlen($content),
            )
        );
    }
}
