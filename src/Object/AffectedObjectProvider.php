<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Object;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\ApplicabilityChecker;
use Generator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AffectedObjectProvider
{
    /**
     * @var array<int, IRoutine>
     */
    protected static $routine_cache = [];

    public function __construct(
        protected IRoutineAssignmentRepository $assignment_repository,
        protected IGeneralRepository $general_repository,
        protected IRoutineRepository $routine_repository,
        protected ApplicabilityChecker $applicability_checker
    ) {
    }

    /**
     * Returns all objects which are "affected" by a routine. This means,
     * objects are assigned directly or by recursion to a routine and it
     * meets all the routine's criteria (the rules).
     *
     * @return AffectedObject[]|Generator
     */
    public function getAffectedObjects(): Generator
    {
        $assignments = $this->assignment_repository->getAllActiveAssignments();
        foreach ($assignments as $assignment) {
            if (null === ($ref_id = $assignment->getRefId())) {
                continue;
            }
            if (null === ($routine_id = $assignment->getRoutineId())) {
                continue;
            }
            if (null === ($routine = $this->getRoutineByCacheOrDatabase($routine_id))) {
                continue;
            }
            $max_depth = ($assignment->isRecursive()) ? PHP_INT_MAX : 1;
            $types = [$routine->getRoutineType()];
            foreach ($this->general_repository->getRepositoryObjectChildren($ref_id, $types, $max_depth) as $object) {
                if ($this->applicability_checker->isApplicable($routine, $object)) {
                    yield new AffectedObject($object, $routine);
                }
            }
        }
    }

    protected function getRoutineByCacheOrDatabase(int $routine_id): ?IRoutine
    {
        if (!isset(self::$routine_cache[$routine_id])) {
            self::$routine_cache[$routine_id] = $this->routine_repository->get($routine_id);
        }

        return self::$routine_cache[$routine_id];
    }
}
