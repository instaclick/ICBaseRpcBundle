<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service;

/**
 * Mock entity.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class MockEntity
{
    /**
     * @var string
     */
    private $foo;

    /**
     * @var integer
     */
    private $bar;

    /**
     * @var \IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockNestedEntity
     */
    private $nestedEntity;

    /**
     * Initialize private attributes.
     *
     * @param integer                                                                 $foo
     * @param string                                                                  $bar
     * @param \IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockNestedEntity $nestedEntity
     */
    public function __construct($foo = null, $bar = null, $nestedEntity = null)
    {
        $this->foo          = $foo;
        $this->bar          = $bar;
        $this->nestedEntity = $nestedEntity ? $nestedEntity : new MockNestedEntity();
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
