<?php
/**
 * @copyright 2014 Instaclick Inc.
 */
namespace IC\Bundle\Base\RpcBundle\Tests\Service;

use IC\Bundle\Base\RpcBundle\Service\NormalizerService;
use IC\Bundle\Base\TestBundle\Test\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Normalizer service test.
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class NormalizerServiceTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->normalizerService    = new NormalizerService();
        $this->filterServiceMock    = $this->createMock('\IC\Bundle\Base\RpcBundle\Service\DeepFilterService');
        $this->validatorServiceMock = $this->createMock('\Symfony\Component\Validator\Validator');

        $this->normalizerService->setFilterService($this->filterServiceMock);
        $this->normalizerService->setValidatorService($this->validatorServiceMock);
    }

    /**
     * Test to model.
     */
    public function testToModel()
    {
        $modelClass = 'IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity';
        $argumentList = array(
            'foo'          => 'Foo',
            'bar'          => 845,
            'nestedEntity' => array(
                'baz' => 'Baz',
                'qux' => 907
            ),
        );

        $this->filterServiceMock
            ->expects($this->once())
            ->method('filterEntity');

        $constraintViolationList = new ConstraintViolationList();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationList));

        $model = $this->normalizerService->toModel($modelClass, $argumentList);

        $this->assertEquals('Foo', $model->foo);
        $this->assertEquals(845, $model->bar);
        $this->assertEquals('Baz', $model->nestedEntity->baz);
        $this->assertEquals(907, $model->nestedEntity->qux);
    }

    /**
     * Test to model with invalid input return null.
     */
    public function testToModelWithInvalidInputReturnNull()
    {
        $modelClass = 'IC\Bundle\Base\RpcBundle\Tests\MockObject\Rpc\Service\MockEntity';
        $argumentList = array();

        $this->filterServiceMock
            ->expects($this->once())
            ->method('filterEntity');

        $constraintViolationList = new ConstraintViolationList();
        $constraintViolation     = new ConstraintViolation('', '', array(), null, '', '');

        $constraintViolationList->add($constraintViolation);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationList));

        $this->assertNull($this->normalizerService->toModel($modelClass, $argumentList));
    }
}
