<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Assignment\RoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineAssignmentRepository implements IRoutineAssignmentRepository
{
    use DTOHelper;

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
    public function get(int $routine_id, int $ref_id) : ?IRoutineAssignment
    {
        $query = "
            SELECT routine_id, ref_id, is_recursive, is_active FROM srlcm_assigned_routine
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
    public function getByRoutineId(int $routine_id, bool $array_data = false) : array
    {
        $query = "
            SELECT routine_id, ref_id, is_recursive, is_active FROM srlcm_assigned_routine
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
            ), $array_data
        );
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

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $ref_id,
                    ]
                )
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutineAssignment $assignment) : IRoutineAssignment
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
    public function delete(IRoutineAssignment $assignment) : bool
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
    public function empty() : IRoutineAssignment
    {
        return new RoutineAssignment();
    }

    /**
     * @param IRoutineAssignment $assignment
     * @return IRoutineAssignment
     */
    protected function insertAssignment(IRoutineAssignment $assignment) : IRoutineAssignment
    {
        if (null === $assignment->getRoutineId() || null === $assignment->getRefId()) {
            throw new LogicException("Assignment must contain routine-id and object (ref-id).");
        }

        $query = "
            INSERT INTO srlcm_assigned_routine (routine_id, ref_id, is_recursive, is_active)
                VALUES (%s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer'],
            [
                $assignment->getRoutineId(),
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

    /**
     * @param array $query_result
     * @return IRoutineAssignment
     */
    protected function transformToDTO(array $query_result) : IRoutineAssignment
    {
        return new RoutineAssignment(
            (int) $query_result[IRoutineAssignment::F_ROUTINE_ID],
            (int) $query_result[IRoutineAssignment::F_REF_ID],
            (bool) $query_result[IRoutineAssignment::F_IS_ACTIVE],
            (bool) $query_result[IRoutineAssignment::F_RECURSIVE]
        );
    }
}