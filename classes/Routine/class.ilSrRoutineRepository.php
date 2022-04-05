<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;

/**
 * This repository is responsible for all routine CRUD operations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineRepository implements IRoutineRepository
{
    use ilSrRepositoryHelper;

    /**
     * @var string mysql datetime format string.
     */
    protected const MYSQL_DATETIME_FORMAT = 'Y-m-d';
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

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

        $result = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer'],
                [$routine_id]
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
    public function getAllByRefId(int $ref_id, bool $array_data = false) : array
    {
        // @TODO: this won't work anymore!
        $query = "
            SELECT 
                routine_id, usr_id, routine_type, origin_type, 
                has_opt_out, elongation, title, creation_date
                FROM srlcm_routine 
                WHERE ref_id IN ({$this->getParentIdsForSqlComparison($ref_id)})
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        if ($array_data) {
            return $results;
        }

        $routines = [];
        foreach ($results as $result) {
            $routines[] = $this->transformToDTO($result);
        }

        return $routines;
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
            DELETE `routine`, rule, relation, notification, whitelist
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