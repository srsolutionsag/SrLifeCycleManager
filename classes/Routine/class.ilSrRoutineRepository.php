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
     * @param ilDBInterface $database
     * @param ilTree        $tree
     */
    public function __construct(ilDBInterface $database, ilTree $tree)
    {
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
                routine_id, ref_id, usr_id, routine_type, origin_type, is_active, 
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
        // gather all parent objects of the given ref-id and
        // add the id itself to the array as well.
        $parents = $this->getParentIdsRecursively($ref_id);
        $parents[] = $ref_id;

        $in_group = implode(',', $parents);
        $query = "
            SELECT 
                routine_id, ref_id, usr_id, routine_type, origin_type, is_active, 
                has_opt_out, elongation, title, creation_date
                FROM srlcm_routine 
                WHERE ref_id IN (%s)
            ;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['text'],
                [$in_group]
            )
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
        // @TODO: test this when routine is associated with everything.

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
            0,
            $owner_id,
            '',
            $origin_type,
            '',
            false,
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
                ref_id = %s, usr_id = %s, routine_type = %s, origin_type = %s, is_active = %s, has_opt_out = %s, 
                elongation = %s, title = %s, creation_date = %s
                WHERE routine_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'text', 'integer', 'integer', 'integer', 'integer', 'text', 'date', 'integer'],
            [
                $routine->getRefId(),
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->isActive(),
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
            INSERT INTO srlcm_routine (routine_id, ref_id, usr_id, routine_type, origin_type, is_active,
                has_opt_out, elongation, title, creation_date)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            ;
        ";

        $routine_id = (int) $this->database->nextId('srlcm_routine');
        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'text', 'integer', 'integer', 'integer', 'integer', 'text', 'date'],
            [
                $routine_id,
                $routine->getRefId(),
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->isActive(),
                $routine->hasOptOut(),
                $routine->getElongation(),
                $routine->getTitle(),
                $routine->getCreationDate()->format(self::MYSQL_DATETIME_FORMAT),
            ]
        );

        return $routine->setRoutineId($routine_id);
    }

    /**
     * @param int $ref_id
     * @return array|null
     */
    protected function getParentIdsRecursively(int $ref_id) : ?array
    {
        static $parents;

        $parent_id = $this->tree->getParentId($ref_id);
        // type-cast is not redundant, as getParentId() returns
        // a string (not as stated by the phpdoc).
        if (null !== $parent_id && 0 < (int) $parent_id) {
            $parents[] = (int) $parent_id;
            $this->getParentIdsRecursively((int) $parent_id);
        }

        return (!empty($parents)) ? $parents : null;
    }

    /**
     * @param array $query_result
     * @return IRoutine
     */
    protected function transformToDTO(array $query_result) : IRoutine
    {
        return new Routine(
            (int) $query_result[IRoutine::F_REF_ID],
            (int) $query_result[IRoutine::F_USER_ID],
            $query_result[IRoutine::F_ROUTINE_TYPE],
            (int) $query_result[IRoutine::F_ORIGIN_TYPE],
            $query_result[IRoutine::F_TITLE],
            (bool) $query_result[IRoutine::F_IS_ACTIVE],
            (bool) $query_result[IRoutine::F_HAS_OPT_OUT],
            DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $query_result[IRoutine::F_CREATION_DATE]),
            (null !== $query_result[IRoutine::F_ELONGATION]) ? (int) $query_result[IRoutine::F_ELONGATION] : null,
            (int) $query_result[IRoutine::F_ROUTINE_ID]
        );
    }
}