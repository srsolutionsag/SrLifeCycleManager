<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Assignment\RoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentRepository implements IRoutineAssignmentRepository
{
    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param IRoutineRepository $routine_repository
     * @param ilDBInterface      $database
     */
    public function __construct(IRoutineRepository $routine_repository, ilDBInterface $database)
    {
        $this->routine_repository = $routine_repository;
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(IRoutine $routine, int $ref_id) : ?IRoutineAssignment
    {
        $query = "
            SELECT routine_id, ref_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE routine_id = %s
                AND ref_id = %s
            ;
        ";

        $result = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer', 'integer'],
                [
                    $routine->getRoutineId(),
                    $ref_id
                ]
            )
        );

        if (!empty($result)) {
            return $this->transformToDTO($result[0]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false) : array
    {
        $query = "
            SELECT routine_id, ref_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE routine_id = %s
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [
                    $routine->getRoutineId(),
                ]
            )
        );

        if (empty($results) || $array_data) {
            return $results;
        }

        $assignments = [];
        foreach ($results as $query_result) {
            $assignments[] = $this->transformToDTO($query_result);
        }

        return $assignments;
    }

    /**
     * @inheritDoc
     */
    public function getByRefId(int $ref_id, bool $array_data = false) : array
    {
        $query = "
            SELECT routine_id, ref_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE ref_id = %s
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [
                    $ref_id,
                ]
            )
        );

        if (empty($results) || $array_data) {
            return $results;
        }

        $assignments = [];
        foreach ($results as $query_result) {
            $assignments[] = $this->transformToDTO($query_result);
        }

        return $assignments;
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutineAssignment $assignment) : IRoutineAssignment
    {
        // abort if the assignment is invalid.
        if (null === $assignment->getRefId()) {
            return $assignment;
        }

        if (null === $this->get($assignment->getRoutine(), $assignment->getRefId())) {
            return $this->insertAssignment($assignment);
        }

        return $this->updateAssignment($assignment);
    }

    /**
     * @inheritDoc
     */
    public function empty(IRoutine $routine) : IRoutineAssignment
    {
        if (null === $routine->getRoutineId()) {
            throw new LogicException("Cannot assign an unsaved routine.");
        }

        return new RoutineAssignment(
            $routine,
            false,
            false,
            null
        );
    }

    /**
     * @param IRoutineAssignment $assignment
     * @return IRoutineAssignment
     */
    protected function insertAssignment(IRoutineAssignment $assignment) : IRoutineAssignment
    {
        $query = "
            INSERT INTO srlcm_assigned_routine (routine_id, ref_id, is_recursive, is_active)
                VALUES (%s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer'],
            [
                $assignment->getRoutine()->getRoutineId(),
                $assignment->getRefId(),
                (int) $assignment->isRecursive(),
                (int) $assignment->isActive(),
            ]
        );

        return $assignment;
    }

    /**
     * @param IRoutineAssignment $assignment
     * @return IRoutineAssignment
     */
    protected function updateAssignment(IRoutineAssignment $assignment) : IRoutineAssignment
    {
        $query = "
            UPDATE srlcm_assigned_routine SET is_recursive = %s, is_active = %s
                WHERE routine_id = %s
                AND ref_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer'],
            [
                (int) $assignment->isRecursive(),
                (int) $assignment->isActive(),
                $assignment->getRoutine()->getRoutineId(),
                $assignment->getRefId(),
            ]
        );

        return $assignment;
    }

    /**
     * @param array $query_result
     * @return IRoutineAssignment
     */
    protected function transformToDTO(array $query_result) : IRoutineAssignment
    {
        $routine = $this->routine_repository->get((int) $query_result[IRoutineAssignment::F_ROUTINE_ID]);
        if (null === $routine) {
            throw new LogicException("Assigned Routine does not exist anymore.");
        }

        return new RoutineAssignment(
            $routine,
            (bool) $query_result[IRoutineAssignment::F_IS_ACTIVE],
            (bool) $query_result[IRoutineAssignment::F_RECURSIVE],
            (int) $query_result[IRoutineAssignment::F_REF_ID]
        );
    }
}