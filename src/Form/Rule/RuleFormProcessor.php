<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IRuleRepository
     */
    protected $repository;

    /**
     * @var IRule
     */
    protected $rule;

    /**
     * @param IRuleRepository        $repository
     * @param ServerRequestInterface $request
     * @param UIForm                 $form
     * @param IRule                  $rule
     */
    public function __construct(
        IRuleRepository $repository,
        ServerRequestInterface $request,
        UIForm $form,
        IRule $rule
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->rule = $rule;
    }

    /**
     * @todo: make this more readable and move this to a constraint
     * @inheritDoc
     */
    public function isValid(array $post_data): bool
    {
        $lhs_value = $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        // ensure that LHS value is only empty if the type is CommonNull.
        if ($this->isSideCommonAttribute(RuleFormBuilder::KEY_LHS_VALUE, $post_data) &&
            $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data) !== CommonNull::class &&
            ('0' === $lhs_value || empty($lhs_value)) // it's important we check '0' before empty, since empty would be true
        ) {
            return false;
        }

        $rhs_value = $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        // ensure that RHS value is only empty if the type is CommonNull.
        if ($this->isSideCommonAttribute(RuleFormBuilder::KEY_RHS_VALUE, $post_data) &&
            $this->getValueTypeBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data) !== CommonNull::class &&
            ('0' === $rhs_value || empty($rhs_value)) // it's important we check '0' before empty, since empty would be true
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function processData(array $post_data): void
    {
        $lhs_value = $this->getValueBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        $rhs_value = $this->getValueBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data);
        $lhs_type = $this->getValueTypeBySide(RuleFormBuilder::KEY_LHS_VALUE, $post_data);
        $rhs_type = $this->getValueTypeBySide(RuleFormBuilder::KEY_RHS_VALUE, $post_data);
        $operator = $post_data[RuleFormBuilder::KEY_OPERATOR];

        $rule = $this->rule;
        $rule
            ->setLhsType($lhs_type)
            ->setLhsValue($lhs_value)
            ->setRhsType($rhs_type)
            ->setRhsValue($rhs_value)
            ->setOperator($operator)
        ;

        $this->repository->store($rule);
    }

    /**
     * Returns the submitted value-type for either the left- or right-hand-side.
     *
     * @param string $side (RuleFormBuilder::KEY_LHS_VALUE|RuleFormBuilder::KEY_RHS_VALUE)
     * @param array  $post_data
     * @return string
     */
    protected function getValueTypeBySide(string $side, array $post_data): string
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
    protected function isSideCommonAttribute(string $side, array $post_data): bool
    {
        return (CommonAttribute::class === $post_data[$side][RuleFormBuilder::INDEX_GROUP_TYPE]);
    }
}
