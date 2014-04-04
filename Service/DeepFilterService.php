<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use DMS\Bundle\FilterBundle\Service\Filter;

/**
 * Deep filter service applies filter annotations on nested objects.
 *
 * @group ICBaseRpc
 * @group Service
 * @group Unit
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class DeepFilterService
{
    /**
     * @var \DMS\Bundle\FilterBundle\Service\Filter
     */
    private $filterService;

    /**
     * Define the filter service.
     *
     * @param \DMS\Bundle\FilterBundle\Service\Filter $filterService
     */
    public function setFilterService(Filter $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Filter entity.
     *
     * @param object $object
     */
    public function filterEntity($object)
    {
        if ($object == null) {
            return;
        }

        $objectReflection       = new \ReflectionClass($object);
        $propertyReflectionList = $objectReflection->getProperties();

        foreach ($propertyReflectionList as $propertyReflection) {
            $propertyReflection->setAccessible(true);

            $propertyValue = $propertyReflection->getValue($object);

            if ( ! is_object($propertyValue)) {
                continue;
            }

            $this->filterEntity($propertyValue);
        }

        $this->filterService->filterEntity($object);
    }
}
