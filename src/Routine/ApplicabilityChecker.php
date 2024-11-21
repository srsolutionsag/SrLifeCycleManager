<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ApplicabilityChecker
{
    /**
     * @var array<int, IRule[]>
     */
    protected static $rule_cache = [];

    /**
     * @var ComparisonFactory
     */
    protected $comparison_factory;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    public function __construct(ComparisonFactory $comparison_factory, IRuleRepository $rule_repository)
    {
        $this->comparison_factory = $comparison_factory;
        $this->rule_repository = $rule_repository;
    }

    /**
     * Returns whether a routine is applicable to an object, which means that
     * all related rules can be applied to the object.
     */
    public function isApplicable(IRoutine $routine, \ilObject $object): bool
    {
        if ($routine->getRoutineType() !== $object->getType()) {
            return false;
        }

        foreach (($rules = $this->getRulesByCacheOrDatabase($routine)) as $rule) {
            $comparison = $this->comparison_factory->getComparison($object, $rule);
            if (!$comparison->isApplicable()) {
                // stop iterating as soon as one rule is not applicable
                // because all rules are AND related.
                return false;
            }
        }

        return !empty($rules);
    }

    /**
     * @return IRule[]
     */
    protected function getRulesByCacheOrDatabase(IRoutine $routine): array
    {
        $routine_id = $routine->getRoutineId();
        if (!isset(self::$rule_cache[$routine_id])) {
            self::$rule_cache[$routine_id] = $this->rule_repository->getByRoutine($routine);
        }

        return self::$rule_cache[$routine_id];
    }
}
