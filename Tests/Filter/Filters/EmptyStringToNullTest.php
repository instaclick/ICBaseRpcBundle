<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Tests\Filter\Filters;

use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RpcBundle\Filter\Filters\EmptyStringToNull as EmptyStringToNullFilter;
use IC\Bundle\Base\RpcBundle\Filter\Rules\EmptyStringToNull as EmptyStringToNullRule;

/**
 * Empty string to null test.
 *
 * @group ICBaseRpcBundleBundle
 * @group Filter
 * @group Unit
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class EmptyStringToNullTest extends TestCase
{
    /**
     * Test filter.
     *
     * @param mixed $value
     * @param mixed $expectedResult
     *
     * @dataProvider provideForFilter
     */
    public function testFilter($value, $expectedResult)
    {
        $rule   = new EmptyStringToNullRule();
        $filter = new EmptyStringToNullFilter();

        $result = $filter->apply($rule, $value);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testFilter.
     *
     * @return array
     */
    public function provideForFilter()
    {
        return array(
            array("", null),
            array("Foobar", "Foobar"),
            array(false, false),
            array(null, null),
            array(0, 0),
        );
    }
}
