<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service;

/**
 * Mock nested entity.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class MockNestedEntity
{
    /**
     * @var string
     */
    private $baz;

    /**
     * @var integer
     */
    private $qux;

    /**
     * Initialize private attributes.
     *
     * @param integer $baz
     * @param string  $qux
     */
    public function __construct($baz = null, $qux = null)
    {
        $this->baz = $baz;
        $this->qux = $qux;
    }

    /**
     * Generic define.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Generic retrieve.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }
}
