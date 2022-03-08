<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Whitelist\WhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This repository is responsible for all whitelist CRUD operations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistRepository implements IWhitelistRepository
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
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(IRoutine $routine, int $ref_id) : ?IWhitelistEntry
    {
        return $this->getWhitelistEntry($routine->getRoutineId(), $ref_id);
    }

    /**
     * @inheritDoc
     */
    public function store(IWhitelistEntry $entry) : IWhitelistEntry
    {
        if (null !== $this->getWhitelistEntry($entry->getRoutineId(), $entry->getRefId())) {
            return $this->updateWhitelistEntry($entry);
        }

        return $this->insertWhitelistEntry($entry);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $ref_id) : bool
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
     * @param IWhitelistEntry $entry
     * @return IWhitelistEntry
     */
    protected function updateWhitelistEntry(IWhitelistEntry $entry) : IWhitelistEntry
    {
        $query = "
            UPDATE srlcm_whitelist SET
                is_opt_out = %s,
                elongation = %s,
                date = %s
                WHERE routine_id = %s
                AND ref_id = %s
            ;        
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'date', 'integer', 'integer'],
            [
                (int) $entry->isOptOut(),
                $entry->getElongation(),
                $entry->getDate()->format(self::MYSQL_DATETIME_FORMAT),
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
    protected function insertWhitelistEntry(IWhitelistEntry $entry) : IWhitelistEntry
    {
        $query = "
            INSERT INTO srlcm_whitelist (routine_id, ref_id, is_opt_out, elongation, date)
                VALUES (%s, %s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer', 'date'],
            [
                $entry->getRoutineId(),
                $entry->getRefId(),
                (int) $entry->isOptOut(),
                $entry->getElongation(),
                $entry->getDate()->format(self::MYSQL_DATETIME_FORMAT)
            ]
        );

        return $entry;
    }

    /**
     * @param int $routine_id
     * @param int $ref_id
     * @return IWhitelistEntry|null
     */
    protected function getWhitelistEntry(int $routine_id, int $ref_id) : ?IWhitelistEntry
    {
        $query = "
            SELECT routine_id, ref_id, is_opt_out, elongation, date
                FROM srlcm_whitelist
                WHERE routine_id = %s
                AND ref_id = %s
            ;
        ";

        $result = $this->database->fetchAll(
            $this->database->queryF(
                $query,
                ['integer', 'integer'],
                [
                    $routine_id,
                    $ref_id,
                ]
            )
        );

        if (!empty($result)) {
            return $this->transformToDTO($result[0]);
        }

        return null;
    }

    /**
     * @param array $query_result
     * @return IWhitelistEntry
     */
    protected function transformToDTO(array $query_result) : IWhitelistEntry
    {
        return new WhitelistEntry(
            (int) $query_result[IWhitelistEntry::F_ROUTINE_ID],
            (int) $query_result[IWhitelistEntry::F_REF_ID],
            (bool) $query_result[IWhitelistEntry::F_IS_OPT_OUT],
            DateTime::createFromFormat(
                self::MYSQL_DATETIME_FORMAT,
                $query_result[IWhitelistEntry::F_DATE]
            ),
            (null !== $query_result[IWhitelistEntry::F_ELONGATION]) ?
                (int) $query_result[IWhitelistEntry::F_ELONGATION] : null
        );
    }
}