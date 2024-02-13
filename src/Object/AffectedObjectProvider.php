<?php

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

    /**
     * @var IRoutineAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var IGeneralRepository
     */
    protected $general_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var ApplicabilityChecker
     */
    protected $applicability_checker;

    public function __construct(
        IRoutineAssignmentRepository $assignment_repository,
        IGeneralRepository $general_repository,
        IRoutineRepository $routine_repository,
        ApplicabilityChecker $applicability_checker
    ) {
        $this->assignment_repository = $assignment_repository;
        $this->general_repository = $general_repository;
        $this->routine_repository = $routine_repository;
        $this->applicability_checker = $applicability_checker;
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
            if (null === ($ref_id = $assignment->getRefId()) ||
                null === ($routine_id = $assignment->getRoutineId()) ||
                null === ($routine = $this->getRoutineByCacheOrDatabase($routine_id))
            ) {
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
