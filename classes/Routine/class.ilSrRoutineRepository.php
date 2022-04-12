<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;
use srag\Plugins\SrLifeCycleManager\Repository\ObjectHelper;

/**
 * This repository is responsible for all routine CRUD operations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineRepository implements IRoutineRepository
{
    use ObjectHelper;
    use DTOHelper;

    /**
     * @var string mysql datetime format string.
     */
    protected const MYSQL_DATETIME_FORMAT = 'Y-m-d';

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface                $database
     * @param ilTree                       $tree
     */
    public function __construct(
        ilDBInterface $database,
        ilTree $tree
    ) {
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id) : ?IRoutine
    {
        $query = "
            SELECT
                routine_id, usr_id, routine_type, origin_type, 
                has_opt_out, elongation, title, creation_date
                FROM srlcm_routine 
                WHERE routine_id = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [$routine_id]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getAll(bool $array_data = false) : array
    {
        $query = "
            SELECT 
                routine_id, usr_id, routine_type, origin_type, 
                has_opt_out, elongation, title, creation_date
                FROM srlcm_routine
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->query($query)
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllByRefId(int $ref_id, bool $array_data = false) : array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type, 
                routine.has_opt_out, routine.elongation, routine.title, routine.creation_date
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
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllActiveByRefId(int $ref_id, bool $array_data = false) : array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type, 
                routine.has_opt_out, routine.elongation, routine.title, routine.creation_date
                FROM srlcm_assigned_routine AS assignment    
                JOIN srlcm_routine AS routine ON `routine`.routine_id = assignment.routine_id
                WHERE assignment.is_active = 1
                AND (
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
            ), $array_data
        );
    }

    /**
     * @todo: the query could most likely be optimized with joins instead
     *        of the current sub-query.
     *
     * @inheritDoc
     */
    public function getAllUnassignedByRefId(int $ref_id, bool $array_data = false) : array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type, 
                routine.has_opt_out, routine.elongation, routine.title, routine.creation_date
                FROM srlcm_routine AS routine
                WHERE routine.routine_id NOT IN (
                    SELECT routine.routine_id 
                        FROM srlcm_assigned_routine AS assignment 
                        JOIN srlcm_routine AS routine ON routine.routine_id = assignment.routine_id
                        WHERE (
                            (assignment.is_recursive = 1 AND assignment.ref_id IN ({$this->getParentIdsForSqlComparison($ref_id)})) OR
                            (assignment.is_recursive = 0 AND assignment.ref_id IN (%s, %s))
                        )
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
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutine $routine) : IRoutine
    {
        if (null === $routine->getRoutineId()) {
            return $this->insertRoutine($routine);
        }

        return $this->updateRoutine($routine);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutine $routine) : bool
    {
        // the query is rather ugly, but since ILIAS doesn't handle
        // fk constraints we have to delete them manually, and I really
        // wanted to do this in one statement.
        $query = "
            DELETE `routine`, rule, relation, notification, whitelist, assignment
                FROM (SELECT %s AS routine_id) AS deletable
                LEFT OUTER JOIN srlcm_routine AS `routine` ON `routine`.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_routine_rule AS relation ON relation.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_rule AS rule ON rule.rule_id = relation.rule_id
                LEFT OUTER JOIN srlcm_notification AS notification ON notification.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_whitelist AS whitelist ON whitelist.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_assigned_routine AS assignment ON assignment.routine_id = deletable.routine_id
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer'],
            [$routine->getRoutineId()]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function empty(int $owner_id, int $origin_type) : IRoutine
    {
        return new Routine(
            $owner_id,
            '',
            $origin_type,
            '',
            false,
            new DateTime()
        );
    }

    /**
     * @param IRoutine $routine
     * @return IRoutine
     */
    protected function updateRoutine(IRoutine $routine) : IRoutine
    {
        $query = "
            UPDATE srlcm_routine SET
                usr_id = %s, routine_type = %s, origin_type = %s, has_opt_out = %s, 
                elongation = %s, title = %s, creation_date = %s
                WHERE routine_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'text', 'integer', 'integer', 'integer', 'text', 'date', 'integer'],
            [
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->hasOptOut(),
                $routine->getElongation(),
                $routine->getTitle(),
                $routine->getCreationDate()->format(self::MYSQL_DATETIME_FORMAT),
                $routine->getRoutineId(),
            ]
        );

        return $routine;
    }

    /**
     * @param IRoutine $routine
     * @return IRoutine
     */
    protected function insertRoutine(IRoutine $routine) : IRoutine
    {
        $query = "
            INSERT INTO srlcm_routine (routine_id, usr_id, routine_type, origin_type,
                has_opt_out, elongation, title, creation_date)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            ;
        ";

        $routine_id = (int) $this->database->nextId('srlcm_routine');
        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'text', 'integer', 'integer', 'integer', 'text', 'date'],
            [
                $routine_id,
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->hasOptOut(),
                $routine->getElongation(),
                $routine->getTitle(),
                $routine->getCreationDate()->format(self::MYSQL_DATETIME_FORMAT),
            ]
        );

        return $routine->setRoutineId($routine_id);
    }

    /**
     * @param array $query_result
     * @return IRoutine
     */
    protected function transformToDTO(array $query_result) : IRoutine
    {
        return new Routine(
            (int) $query_result[IRoutine::F_USER_ID],
            $query_result[IRoutine::F_ROUTINE_TYPE],
            (int) $query_result[IRoutine::F_ORIGIN_TYPE],
            $query_result[IRoutine::F_TITLE],
            (bool) $query_result[IRoutine::F_HAS_OPT_OUT],
            DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $query_result[IRoutine::F_CREATION_DATE]),
            (null !== $query_result[IRoutine::F_ELONGATION]) ? (int) $query_result[IRoutine::F_ELONGATION] : null,
            (int) $query_result[IRoutine::F_ROUTINE_ID]
        );
    }
}