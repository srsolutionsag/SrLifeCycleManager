<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IWhitelistRepository;
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
    public function extendObjectByRefId(IRoutine $routine, int $ref_id) : bool
    {
        if (1 > $routine->getElongation()) {
            return false;
        }

        if (!$this->isObjectExtended($routine, $ref_id)) {
            return $this->insertWhitelistEntry($routine, $ref_id, false, $routine->getElongation());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isObjectExtended(IRoutine $routine, int $ref_id) : bool
    {
        $query = "
            SELECT COUNT(ref_id) AS entries FROM srlcm_whitelist
                WHERE elongation != NULL
                AND is_opt_out != 1
                AND routine_id = %s
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
            return (0 < (int) $result[0]['entries']);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function optOutObjectByRefId(IRoutine $routine, int $ref_id) : bool
    {
        if (!$routine->hasOptOut()) {
            return false;
        }

        if (!$this->isObjectOptedOut($routine, $ref_id)) {
            return $this->insertWhitelistEntry($routine, $ref_id, true);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isObjectOptedOut(IRoutine $routine, int $ref_id) : bool
    {
        $query = "
            SELECT COUNT(ref_id) AS entries FROM srlcm_whitelist
                WHERE is_opt_out = 1
                AND routine_id = %s
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
            return (0 < (int) $result[0]['entries']);
        }

        return false;
    }

    /**
     * Helper function that inserts a new whitelist entry.
     *
     * @param IRoutine $routine
     * @param int      $ref_id
     * @param bool     $is_opt_out
     * @param int|null $elongation
     * @return bool
     */
    protected function insertWhitelistEntry(IRoutine $routine, int $ref_id, bool $is_opt_out, int $elongation = null) : bool
    {
        $query = "
            INSERT INTO srlcm_whitelist (routine_id, ref_id, is_opt_out, elongation)
                VALUES (%s, %s, %s, %s)
            ;
        ";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'integer', 'integer'],
            [
                $routine->getRoutineId(),
                $ref_id,
                (int) $is_opt_out,
                $elongation
            ]
        );

        return true;
    }
}