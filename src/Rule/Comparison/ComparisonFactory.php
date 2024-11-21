<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Comparison;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Equal;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Greater;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\InArray;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\InValue;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Lesser;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Operation\Unequal;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\RessourceFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use LogicException;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ComparisonFactory
{
    protected AttributeFactory $attribute_factory;

    protected RessourceFactory $ressource_factory;

    public function __construct(RessourceFactory $ressource_factory, AttributeFactory $attribute_factory)
    {
        $this->ressource_factory = $ressource_factory;
        $this->attribute_factory = $attribute_factory;
    }

    public function getComparison(ilObject $object, IRule $rule): IComparison
    {
        switch ($rule->getOperator()) {
            case IRule::OPERATOR_EQUAL:
                return $this->equal($object, $rule);
            case IRule::OPERATOR_NOT_EQUAL:
                return $this->unequal($object, $rule);
            case IRule::OPERATOR_GREATER:
                return $this->greater($object, $rule, true);
            case IRule::OPERATOR_GREATER_EQUAL:
                return $this->greater($object, $rule, false);
            case IRule::OPERATOR_LESSER:
                return $this->lesser($object, $rule, true);
            case IRule::OPERATOR_LESSER_EQUAL:
                return $this->lesser($object, $rule, false);
            case IRule::OPERATOR_CONTAINS:
                return $this->inValue($object, $rule);
            case IRule::OPERATOR_IN_ARRAY:
                return $this->inArray($object, $rule);

            default:
                throw new LogicException("Comparison for operator '{$rule->getOperator()}' is not supported.");
        }
    }

    public function equal(ilObject $object, IRule $rule): Equal
    {
        return new Equal(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule
        );
    }

    public function unequal(ilObject $object, IRule $rule): Unequal
    {
        return new Unequal(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule
        );
    }

    public function greater(ilObject $object, IRule $rule, bool $strict): Greater
    {
        return new Greater(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule,
            $strict
        );
    }

    public function lesser(ilObject $object, IRule $rule, bool $strict): Lesser
    {
        return new Lesser(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule,
            $strict
        );
    }

    public function inValue(ilObject $object, IRule $rule): InValue
    {
        return new InValue(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule
        );
    }

    public function inArray(ilObject $object, IRule $rule): InArray
    {
        return new InArray(
            $this->attribute_factory,
            $this->ressource_factory->getRessource($object),
            $rule
        );
    }
}
