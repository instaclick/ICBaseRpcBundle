<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Filter\Filters;

use DMS\Filter\Rules\Rule;
use DMS\Filter\Filters\BaseFilter;

/**
 * Empty string to null filter.
 *
 * @Annotation
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class EmptyStringToNull extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(Rule $rule, $value)
    {
        return $value === "" ? null : $value;
    }
}
