<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RpcBundle\Service;

use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator;

/**
 * Model factory service.
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class ModelFactoryService
{
    /**
     * @var \IC\Bundle\Base\RpcBundle\Service\DeepFilterService
     */
    private $filterService;

    /**
     * @var \Symfony\Component\Validator\Validator
     */
    private $validatorService;

    /**
     * Define the filter service.
     *
     * @param \IC\Bundle\Base\RpcBundle\Service\DeepFilterService $filterService
     */
    public function setFilterService(DeepFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Define the validator service.
     *
     * @param \Symfony\Component\Validator\Validator $validatorService
     */
    public function setValidatorService(Validator $validatorService)
    {
        $this->validatorService = $validatorService;
    }

    /**
     * Create a model from an array by hydrating, filtering and validating the data.
     *
     * @param string $modelClass
     * @param array  $argumentList
     *
     * @return array
     */
    public function createModel($modelClass, array $argumentList)
    {
        $model = new $modelClass();

        $this->hydrateModel($model, $argumentList);
        $this->filterService->filterEntity($model);

        $constraintViolationList = $this->validatorService->validate($model);

        if ($constraintViolationList->count()) {
            return null;
        }

        return $model;
    }

    /**
     * Hydrate a model using the argument list.
     *
     * @param object $model
     * @param array  $argumentList
     */
    private function hydrateModel($model, $argumentList)
    {
        foreach ($argumentList as $argument => $value) {
            $this->hydrateProperty($model, $argument, $value);
        }
    }

    /**
     * Hydrate property in a model.
     *
     * @param object $model
     * @param string $property
     * @param mixed  $value
     */
    private function hydrateProperty($model, $property, $value)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        // If the value to be set is array and the model attribute does not hold an array we need to
        // recurse and set the properties using property access again.
        if (is_array($value) && ! is_array($accessor->getValue($model, $property))) {
            $this->hydrateModel($accessor->getValue($model, $property), $value);

            return;
        }

        try {
            $accessor->setValue($model, $property, $value);
        } catch (NoSuchIndexException $exception) {
            // noop
        } catch (NoSuchPropertyException $exception) {
            // noop
        } catch (UnexpectedTypeException $exception) {
            // noop
        }
    }
}
