<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Rule\Rule;

/**
 * Class ilSrRuleRepository
 */
class ilSrRuleRepository implements IRuleRepository
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(int $id) : ?IRule
    {
        /**
         * @var $ar_rule ilSrRule
         */
        $ar_rule = ilSrRule::find($id);
        return (null !== $ar_rule) ?
            $this->transformToDTO($ar_rule) : null
        ;
    }

    /**
     * @inheritDoc
     */
    public function store(IRule $rule) : IRule
    {
        // fetch existing rule or create new AR instance
        if (null !== $rule->getId()) {
            $ar_rule = ilSrRule::find($rule->getId()) ?? new ilSrRule();
        } else {
            $ar_rule = new ilSrRule();
        }

        $ar_rule
            ->setLhsType($rule->getLhsType())
            ->setLhsValue($rule->getLhsValue())
            ->setOperator($rule->getOperator())
            ->setRhsType($rule->getRhsType())
            ->setRhsValue($rule->getRhsValue())
            ->store()
        ;

        return $this->transformToDTO($ar_rule);
    }

    /**
     * @inheritDoc
     */
    public function getAllAsDTO() : ?array
    {
        /**
         * @var $ar_rules ilSrRule[]
         */
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return null;

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToDTO($ar_rule);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getAllAsArray() : array
    {
        /**
         * @var $ar_rules ilSrRule[]
         */
        $ar_rules = ilSrRule::get();
        if (empty($ar_rules)) return [];

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            $rules[] = $this->transformToArray($ar_rule);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getAllForValueTypes(array $value_types) : ?array
    {
        global $DIC;

        $query_types = [];
        $query_values = [];
        $query = "SELECT * FROM " . ilSrRule::TABLE_NAME;

        // @TODO: replace this shit with IN operator
        for ($i = 0, $type_count = count($value_types); $i < $type_count; $i++) {
            if ($i === 0) {
                $query .= " WHERE lhs_type = %s";
                $query .= " OR rhs_type = %s";
            } else {
                $query .= " OR lhs_type = %s";
                $query .= " OR rhs_type = %s";
            }

            // add query-type and query-value twice
            $query_types[] = 'text';
            $query_types[] = 'text';
            $query_values[] = $value_types[$i];
            $query_values[] = $value_types[$i];
        }

        $results = $DIC->database()->fetchAll(
            $DIC->database()->queryF($query, $query_types, $query_values)
        );

        if (empty($results)) return null;

        $rules = [];
        foreach ($results as $rule_data) {
            $rules[] = $this->transformToDTO($rule_data);
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function delete(IRule $rule) : bool
    {
        // nothing to do, rule hasn't been saved to the
        // database yet
        if (null === $rule->getId()) return true;

        // abort if the given rule was not found in the
        // database.
        $ar_rule = ilSrRule::find($rule->getId());
        if (null === $ar_rule) return false;

        $ar_routines = ilSrRoutineRule::where([
            ilSrRoutineRule::F_RULE_ID => $rule->getId(),
        ])->get();

        // delete all routine relations of this rule.
        if (!empty($ar_routines)) {
            foreach ($ar_routines as $relation) {
                $relation->delete();
            }
        }

        // finally, delete the rule itself and return.
        $ar_rule->delete();
        return false;
    }

    /**
     * @param IRule $rule
     * @return IRule
     */
    public function transformToDTO(IRule $rule) : IRule
    {
        return new Rule(
            $rule->getId(),
            $rule->getLhsType(),
            $rule->getLhsValue(),
            $rule->getOperator(),
            $rule->getRhsType(),
            $rule->getRhsValue()
        );
    }

    /**
     * @param IRule $rule
     * @return array
     */
    public function transformToArray(IRule $rule) : array
    {
        return  [
            IRule::F_ID => $rule->getId(),
            IRule::F_LHS_TYPE => $rule->getLhsType(),
            IRule::F_LHS_VALUE => $rule->getLhsValue(),
            IRule::F_OPERATOR => $rule->getOperator(),
            IRule::F_RHS_TYPE => $rule->getRhsType(),
            IRule::F_RHS_VALUE => $rule->getRhsValue(),
        ];
    }
}