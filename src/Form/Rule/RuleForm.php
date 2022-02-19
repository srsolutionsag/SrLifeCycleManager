<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\IRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineAwareRule;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Form\AbstractForm;

use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleForm extends AbstractForm
{
    /**
     * @var IRoutineAwareRule
     */
    protected $rule;

    /**
     * @param IRepository     $repository
     * @param Renderer        $renderer
     * @param RuleFormBuilder $builder
     */
    public function __construct(
        IRepository $repository,
        Renderer $renderer,
        RuleFormBuilder $builder
    ) {
        parent::__construct($repository, $renderer, $builder);

        $this->rule = $builder->getRule();
    }

    /**
     * @inheritDoc
     */
    public function isValid(array $post_data) : bool
    {
        // ensure that LHS value is only empty if the type is CommonNull.
        if ($this->isSideCommonAttribute(RuleFormBuilder::KEY_LHS_VALUE, $post_data) &&
            $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data) !== CommonNull::class &&
            empty($this->getValueBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data))
        ) {
            return false;
        }

        // ensure that RHS value is only empty if the type is CommonNull.
        if ($this->isSideCommonAttribute(RuleFormBuilder::KEY_RHS_VALUE, $post_data) &&
            $this->getValueTypeBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data) !== CommonNull::class &&
            empty($this->getValueBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data))
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(array $post_data) : void
    {
        $lhs_value = $this->getValueBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        $rhs_value = $this->getValueBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data);
        $lhs_type  = $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        $rhs_type  = $this->getValueTypeBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data);
        $operator  = $post_data[RuleFormBuilder::KEY_OPERATOR];

        $rule = $this->rule;
        $rule
            ->setLhsType($lhs_type)
            ->setLhsValue($lhs_value)
            ->setRhsType($rhs_type)
            ->setRhsValue($rhs_value)
            ->setOperator($operator)
        ;

        $this->repository->rule()->store($rule);
    }

    /**
     * Returns the submitted value-type for either the left- or right-hand-side.
     *
     * @param string $side (RuleFormBuilder::KEY_LHS_VALUE|RuleFormBuilder::KEY_RHS_VALUE)
     * @param array  $post_data
     * @return string
     */
    protected function getValueTypeBySide(string $side, array $post_data) : string
    {
        // because common value types must be differentiated to a
        // further degree, the switchable group data of them contains
        // another input that contains the actual type.
        return ($this->isSideCommonAttribute($side, $post_data)) ?
            $post_data[$side][RuleFormBuilder::INDEX_GROUP_VALUE][RuleFormBuilder::KEY_ATTR_TYPE] :
            $post_data[$side][RuleFormBuilder::INDEX_GROUP_TYPE]
        ;
    }

    /**
     * Returns the submitted attribute-value for either the left- or right-hand-side.
     *
     * @param string $side (RuleFormBuilder::KEY_LHS_VALUE|RuleFormBuilder::KEY_RHS_VALUE)
     * @param array  $post_data
     * @return mixed
     */
    protected function getValueBySide(string $side, array $post_data)
    {
        return $post_data[$side][RuleFormBuilder::INDEX_GROUP_VALUE][RuleFormBuilder::KEY_ATTR_VALUE];
    }

    /**
     * Returns whether the left- or right-hand-side is a common attribute.
     *
     * @param string $side
     * @param array  $post_data
     * @return bool
     */
    protected function isSideCommonAttribute(string $side, array $post_data) : bool
    {
        return (CommonAttribute::class === $post_data[$side][RuleFormBuilder::INDEX_GROUP_TYPE]);
    }
}