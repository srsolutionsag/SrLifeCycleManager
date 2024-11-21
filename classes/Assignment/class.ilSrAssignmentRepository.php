<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Assignment\RoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Repository\ObjectHelper;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrAssignmentRepository implements IRoutineAssignmentRepository
{
    use ObjectHelper;
    use DTOHelper;

    protected \ilDBInterface $database;

    public function __construct(ilDBInterface $database, ilTree $tree)
    {
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id, int $ref_id): ?IRoutineAssignment
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE routine_id = %s
                AND ref_id = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer'],
                    [
                        $routine_id,
                        $ref_id
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllActiveAssignments(): array
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE is_active = 1
                AND ref_id IS NOT NULL
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll($this->database->query($query))
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllByRoutineId(int $routine_id, bool $array_data = false): array
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE routine_id = %s
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $routine_id,
                    ]
                )
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllByRefId(int $ref_id, bool $array_data = false): array
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_recursive, is_active FROM srlcm_assigned_routine
                WHERE ref_id = %s
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $ref_id,
                    ]
                )
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllWithJoinedDataByRefId(int $ref_id): array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type, 
                routine.has_opt_out, routine.elongation, routine.title, routine.creation_date,
                assignment.routine_id, assignment.ref_id, assignment.usr_id, assignment.is_recursive, assignment.is_active
                FROM srlcm_assigned_routine AS assignment
                JOIN srlcm_routine AS routine ON `routine`.routine_id = assignment.routine_id
                WHERE (
                    (assignment.is_recursive = 1 AND assignment.ref_id IN ({$this->getParentIdsForSqlComparison($ref_id)})) OR
                    (assignment.is_recursive = 0 AND assignment.ref_id IN (%s, %s))
                )
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer'],
                    [
                        $this->getParentId($ref_id),
                        $ref_id,
                    ]
                )
            ),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutineAssignment $assignment): IRoutineAssignment
    {
        $routine_id = $assignment->getRoutineId();
        $ref_id = $assignment->getRefId();

        if (null !== $ref_id &&
            null !== $routine_id &&
            null !== $this->get($routine_id, $ref_id)
        ) {
            return $this->updateAssignment($assignment);
        }

        return $this->insertAssignment($assignment);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutineAssignment $assignment): bool
    {
        // the assignment cannot be stored yet if there aren't both
        // routine- and ref-id assigned.
        if (null === $assignment->getRoutineId() || null === $assignment->getRefId()) {
            return true;
        }

        $query = "DELETE FROM srlcm_assigned_routine WHERE routine_id = %s AND ref_id = %s";
        $this->database->manipulateF(
            $query,
            ['integer', 'integer'],
            [
                $assignment->getRoutineId(),
                $assignment->getRefId(),
            ]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function empty(int $user_id): IRoutineAssignment
    {
        return new RoutineAssignment($user_id);
    }

    protected function insertAssignment(IRoutineAssignment $assignment): IRoutineAssignment
    {
        if (null === $assignment->getRoutineId() || null === $assignment->getRefId()) {
            throw new LogicException("Assignment must contain routine-id and object (ref-id).");
        }

        $query = "
            INSERT INTO srlcm_assigned_routine (routine_id, ref_id, usr_id, is_recursive, is_active)
                VALUES (%s, %s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [
                $assignment->getRoutineId(),
                $assignment->getRefId(),
                $assignment->getUserId(),
                (int) $assignment->isRecursive(),
                (int) $assignment->isActive(),
            ]
        );

        return $assignment;
    }

    protected function updateAssignment(IRoutineAssignment $assignment): IRoutineAssignment
    {
        if (null === $assignment->getRoutineId() || null === $assignment->getRefId()) {
            throw new LogicException("Assignment must contain routine-id and object (ref-id).");
        }

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
                $assignment->getRoutineId(),
                $assignment->getRefId(),
            ]
        );

        return $assignment;
    }

    protected function transformToDTO(array $query_result): IRoutineAssignment
    {
        return new RoutineAssignment(
            (int) $query_result[IRoutineAssignment::F_USER_ID],
            (int) $query_result[IRoutineAssignment::F_ROUTINE_ID],
            (int) $query_result[IRoutineAssignment::F_REF_ID],
            (bool) $query_result[IRoutineAssignment::F_IS_ACTIVE],
            (bool) $query_result[IRoutineAssignment::F_RECURSIVE]
        );
    }
}
