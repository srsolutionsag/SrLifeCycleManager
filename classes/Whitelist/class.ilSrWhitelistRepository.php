<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Whitelist\WhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;

/**
 * This repository is responsible for all whitelist CRUD operations.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistRepository implements IWhitelistRepository
{
    use DateTimeHelper;
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
    public function get(IRoutine $routine, int $ref_id): ?IWhitelistEntry
    {
        return $this->getWhitelistEntry(
            $routine->getRoutineId() ?? 0,
            $ref_id
        );
    }

    /**
     * @inheritDoc
     */
    public function getByRoutine(IRoutine $routine, bool $array_data = false): array
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_opt_out, expiry_date, date FROM srlcm_whitelist
                WHERE routine_id = %s
            ;
        ";

        return $this->returnAllQueryResults(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer'],
                    [
                        $routine->getRoutineId() ?? 0,
                    ]
                )
            ), $array_data
        );
    }

    /**
     * @inheritDoc
     */
    public function store(IWhitelistEntry $entry): IWhitelistEntry
    {
        if (null !== $this->getWhitelistEntry($entry->getRoutineId(), $entry->getRefId())) {
            return $this->updateWhitelistEntry($entry);
        }

        return $this->insertWhitelistEntry($entry);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $ref_id): bool
    {
        $query = "DELETE FROM srlcm_whitelist WHERE ref_id = %s;";
        $this->database->manipulateF(
            $query,
            ['integer'],
            [$ref_id]
        );

        return true;
    }

    /**
     * @param IRoutine $routine
     * @param int      $ref_id
     * @param int      $user_id
     * @return IWhitelistEntry
     */
    public function empty(IRoutine $routine, int $ref_id, int $user_id) : IWhitelistEntry
    {
        return new WhitelistEntry(
            $routine->getRoutineId() ?? 0,
            $ref_id,
            $user_id,
            false,
            $this->getCurrentDate()
        );
    }

    /**
     * @param IWhitelistEntry $entry
     * @return IWhitelistEntry
     */
    protected function updateWhitelistEntry(IWhitelistEntry $entry): IWhitelistEntry
    {
        $query = "
            UPDATE srlcm_whitelist SET
                usr_id = %s,
                is_opt_out = %s,
                expiry_date = %s,
                date = %s
                WHERE routine_id = %s
                AND ref_id = %s
            ;        
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'date', 'date', 'integer', 'integer'],
            [
                $entry->getUserId(),
                (int) $entry->isOptOut(),
                $this->getMysqlDateString($entry->getExpiryDate()),
                $this->getMysqlDateString($entry->getDate()),
                $entry->getRoutineId(),
                $entry->getRefId(),
            ]
        );

        return $entry;
    }

    /**
     * @param IWhitelistEntry $entry
     * @return IWhitelistEntry
     */
    protected function insertWhitelistEntry(IWhitelistEntry $entry): IWhitelistEntry
    {
        $query = "
            INSERT INTO srlcm_whitelist (routine_id, ref_id, usr_id, is_opt_out, expiry_date, date)
                VALUES (%s, %s, %s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer', 'date', 'date'],
            [
                $entry->getRoutineId(),
                $entry->getRefId(),
                $entry->getUserId(),
                (int) $entry->isOptOut(),
                $this->getMysqlDateString($entry->getExpiryDate()),
                $this->getMysqlDateString($entry->getDate()),
            ]
        );

        return $entry;
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return IWhitelistEntry|null
     */
    protected function getWhitelistEntry(int $routine_id, int $ref_id): ?IWhitelistEntry
    {
        $query = "
            SELECT routine_id, ref_id, usr_id, is_opt_out, expiry_date, date
                FROM srlcm_whitelist
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
                        $ref_id,
                    ]
                )
            )
        );
    }

    /**
     * @param array $query_result
     * @return IWhitelistEntry
     */
    protected function transformToDTO(array $query_result): IWhitelistEntry
    {
        return new WhitelistEntry(
            (int) $query_result[IWhitelistEntry::F_ROUTINE_ID],
            (int) $query_result[IWhitelistEntry::F_REF_ID],
            (int) $query_result[IWhitelistEntry::F_USER_ID],
            (bool) $query_result[IWhitelistEntry::F_IS_OPT_OUT],
            $this->getRequiredDateByQueryResult($query_result, IWhitelistEntry::F_DATE),
            $this->getDateByQueryResult($query_result, IWhitelistEntry::F_EXPIRY_DATE)
        );
    }
}