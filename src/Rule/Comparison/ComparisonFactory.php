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
    public function __construct(
        protected RessourceFactory $ressource_factory,
        protected AttributeFactory $attribute_factory
    ) {
    }

    public function getComparison(ilObject $object, IRule $rule): IComparison
    {
        return match ($rule->getOperator()) {
            IRule::OPERATOR_EQUAL => $this->equal($object, $rule),
            IRule::OPERATOR_NOT_EQUAL => $this->unequal($object, $rule),
            IRule::OPERATOR_GREATER => $this->greater($object, $rule, true),
            IRule::OPERATOR_GREATER_EQUAL => $this->greater($object, $rule, false),
            IRule::OPERATOR_LESSER => $this->lesser($object, $rule, true),
            IRule::OPERATOR_LESSER_EQUAL => $this->lesser($object, $rule, false),
            IRule::OPERATOR_CONTAINS => $this->inValue($object, $rule),
            IRule::OPERATOR_IN_ARRAY => $this->inArray($object, $rule),
            default => throw new LogicException("Comparison for operator '{$rule->getOperator()}' is not supported."),
        };
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
