<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine\Provider;

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineProvider
{
    /**
     * @var ComparisonFactory
     */
    protected $comparison_factory;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @param ComparisonFactory $comparison_factory
     * @param IRoutineRepository $routine_repository
     * @param IRuleRepository $rule_repository
     */
    public function __construct(
        ComparisonFactory $comparison_factory,
        IRoutineRepository $routine_repository,
        IRuleRepository $rule_repository
    ) {
        $this->comparison_factory = $comparison_factory;
        $this->routine_repository = $routine_repository;
        $this->rule_repository = $rule_repository;
    }

    /**
     * @return IRoutine[]
     */
    public function getAffectingRoutines(ilObject $object) : array
    {
        return array_filter(
            $this->routine_repository->getAllForComparison(
                $object->getRefId(),
                $object->getType()
            ),
            function (IRoutine $routine) use ($object) : bool {
                return $this->isApplicable($routine, $object);
            }
        );
    }

    /**
     * @param IRoutine $routine
     * @param ilObject $object
     * @return bool
     */
    protected function isApplicable(IRoutine $routine, ilObject $object) : bool
    {
        if (!in_array($object->getType(), IRoutine::ROUTINE_TYPES, true)) {
            return false;
        }

        $rules = $this->rule_repository->getByRoutine($routine);

        // if there are no rules yet, the routine is not applicable.
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            $comparison = $this->comparison_factory->getComparison($object, $rule);
            if (!$comparison->isApplicable()) {
                // stop iterating as soon as one rule is not applicable
                // because all rules are AND related.
                return false;
            }
        }

        return true;
    }
}
