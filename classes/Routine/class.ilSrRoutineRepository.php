<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;
use srag\Plugins\SrLifeCycleManager\Repository\ObjectHelper;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * This repository is responsible for all routine CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineRepository implements IRoutineRepository
{
    use DateTimeHelper;
    use ObjectHelper;
    use DTOHelper;

    protected IReminderRepository $reminder_repository;

    protected IWhitelistRepository $whitelist_repository;

    protected \ilDBInterface $database;

    /**
     * @param IReminderRepository  $reminder_repository
     * @param IWhitelistRepository $whitelist_repository
     * @param ilDBInterface        $database
     * @param ilTree               $tree
     */
    public function __construct(
        IReminderRepository $reminder_repository,
        IWhitelistRepository $whitelist_repository,
        ilDBInterface $database,
        ilTree $tree
    ) {
        $this->reminder_repository = $reminder_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id): ?IRoutine
    {
        $query = "
            SELECT
                routine_id, usr_id, routine_type, origin_type, 
                has_opt_out, elongation, elongation_cooldown, title,
                creation_date
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
    public function getAll(bool $array_data = false): array
    {
        $query = "
            SELECT 
                routine_id, usr_id, routine_type, origin_type, 
                has_opt_out, elongation, elongation_cooldown, title,
                creation_date
                FROM srlcm_routine
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->query($query)
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
            SELECT
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type,
                routine.has_opt_out, routine.elongation, routine.elongation_cooldown, routine.title,
                routine.creation_date
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
            $array_data
        );
    }

    /**
     * Fetches all existing routines from the database that affect the
     * given ref-id AND are active.
     *
     * To retrieve routines as array-data, true can be passed as an argument
     * (usually required by ilTableGUI).
     *
     * @param int  $ref_id
     * @param bool $array_data
     * @return IRoutine[]
     */
    public function getAllActiveByRefId(int $ref_id, bool $array_data = false): array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type,
                routine.has_opt_out, routine.elongation, routine.elongation_cooldown, routine.title,
                routine.creation_date
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
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllForComparison(int $ref_id, string $type): array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type,
                routine.has_opt_out, routine.elongation, routine.elongation_cooldown, routine.title,
                routine.creation_date
                FROM srlcm_assigned_routine AS assignment
                JOIN srlcm_routine AS routine ON routine.routine_id = assignment.routine_id
                WHERE assignment.is_active = 1
                AND routine.routine_type = %s
                AND (
                    (assignment.is_recursive = 1 AND assignment.ref_id IN ({$this->getParentIdsForSqlComparison($ref_id)})) OR
                    (assignment.is_recursive = 0 AND assignment.ref_id IN (%s, %s))
                ) 
                AND routine.routine_id NOT IN (
                    SELECT whitelist.routine_id FROM srlcm_whitelist AS whitelist
                        WHERE whitelist.routine_id = assignment.routine_id
                        AND whitelist.ref_id = %s
                        AND whitelist.is_opt_out = 1
                )
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['text', 'integer', 'integer', 'integer'],
                    [
                        $type,
                        $this->getParentId($ref_id),
                        $ref_id,
                        $ref_id,
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllUnassigned(int $ref_id, bool $array_data = false): array
    {
        $query = "
            SELECT 
                routine.routine_id, routine.usr_id, routine.routine_type, routine.origin_type,
                routine.has_opt_out, routine.elongation, routine.elongation_cooldown, routine.title,
                routine.creation_date
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
            ),
            $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function getDeletionDate(IRoutine $routine, int $ref_id): DateTimeImmutable
    {
        $previous_reminder = $this->reminder_repository->getRecentlySent($routine, $ref_id);
        $last_reminder = $this->reminder_repository->getLastByRoutine($routine);

        $today = $this->getCurrentDate();

        // if there is no last reminder, there is no reminder at all,
        // therefore the deletion day is today.
        if (null === $last_reminder) {
            return $today;
        }

        // if the last reminder has been sent and it is elapsed today,
        // then the deletion day is today as well.
        if (null !== $previous_reminder &&
            $previous_reminder->isElapsed($today) &&
            $previous_reminder->getNotificationId() === $last_reminder->getNotificationId()
        ) {
            return $today;
        }

        // if there is a previously sent reminder, the amount of days
        // before deletion can be added to its notified date.
        if (null !== $previous_reminder) {
            return $previous_reminder->getNotifiedDate()->add(
                new DateInterval("P{$previous_reminder->getDaysBeforeDeletion()}D")
            );
        }

        // if there is no presiously sent reminder, the deletion day
        // will be today + the amount of days before deletion of the
        // first reminder.
        $first_reminder = $this->reminder_repository->getFirstByRoutine($routine) ?? $last_reminder;
        return $today->add(new DateInterval("P{$first_reminder->getDaysBeforeDeletion()}D"));
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutine $routine): IRoutine
    {
        if (null === $routine->getRoutineId()) {
            return $this->insertRoutine($routine);
        }

        return $this->updateRoutine($routine);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutine $routine): bool
    {
        // the query is rather ugly, but since ILIAS doesn't handle
        // fk constraints we have to delete them manually, and I really
        // wanted to do this in one statement.
        $query = "
            DELETE `routine`, rule, relation, notification, whitelist, assignment, token
                FROM (SELECT %s AS routine_id) AS deletable
                LEFT OUTER JOIN srlcm_routine AS `routine` ON `routine`.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_routine_rule AS relation ON relation.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_rule AS rule ON rule.rule_id = relation.rule_id
                LEFT OUTER JOIN srlcm_notification AS notification ON notification.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_whitelist AS whitelist ON whitelist.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_assigned_routine AS assignment ON assignment.routine_id = deletable.routine_id
                LEFT OUTER JOIN srlcm_tokens AS token ON token.routine_id = deletable.routine_id
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
    public function empty(int $owner_id, int $origin_type): IRoutine
    {
        return new Routine(
            $owner_id,
            '',
            $origin_type,
            '',
            false,
            $this->getCurrentDate()
        );
    }

    /**
     * @param IRoutine $routine
     * @return IRoutine
     */
    protected function updateRoutine(IRoutine $routine): IRoutine
    {
        $query = "
            UPDATE srlcm_routine SET
                usr_id = %s, routine_type = %s, origin_type = %s, has_opt_out = %s, 
                elongation = %s, elongation_cooldown = %s, title = %s, creation_date = %s
                WHERE routine_id = %s
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'text', 'integer', 'integer', 'integer', 'integer', 'text', 'date', 'integer'],
            [
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->hasOptOut(),
                $routine->getElongation(),
                $routine->getElongationCooldown(),
                $routine->getTitle(),
                $this->getMysqlDateString($routine->getCreationDate()),
                $routine->getRoutineId(),
            ]
        );

        return $routine;
    }

    /**
     * @param IRoutine $routine
     * @return IRoutine
     */
    protected function insertRoutine(IRoutine $routine): IRoutine
    {
        $query = "
            INSERT INTO srlcm_routine (routine_id, usr_id, routine_type, origin_type,
                has_opt_out, elongation, elongation_cooldown, title, creation_date)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            ;
        ";

        $routine_id = (int) $this->database->nextId('srlcm_routine');
        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'text', 'integer', 'integer', 'integer', 'integer', 'text', 'date'],
            [
                $routine_id,
                $routine->getOwnerId(),
                $routine->getRoutineType(),
                $routine->getOrigin(),
                $routine->hasOptOut(),
                $routine->getElongation(),
                $routine->getElongationCooldown(),
                $routine->getTitle(),
                $this->getMysqlDateString($routine->getCreationDate()),
            ]
        );

        return $routine->setRoutineId($routine_id);
    }

    /**
     * @param array $query_result
     * @return IRoutine
     */
    protected function transformToDTO(array $query_result): IRoutine
    {
        return new Routine(
            (int) $query_result[IRoutine::F_USER_ID],
            $query_result[IRoutine::F_ROUTINE_TYPE],
            (int) $query_result[IRoutine::F_ORIGIN_TYPE],
            $query_result[IRoutine::F_TITLE],
            (bool) $query_result[IRoutine::F_HAS_OPT_OUT],
            $this->getRequiredDateByQueryResult($query_result, IRoutine::F_CREATION_DATE),
            (null !== $query_result[IRoutine::F_ELONGATION]) ? (int) $query_result[IRoutine::F_ELONGATION] : null,
            (null !== $query_result[IRoutine::F_COOLDOWN]) ? (int) $query_result[IRoutine::F_COOLDOWN] : null,
            (int) $query_result[IRoutine::F_ROUTINE_ID]
        );
    }
}
