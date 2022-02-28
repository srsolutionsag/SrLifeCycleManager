<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\_SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineWhitelist implements IRoutineWhitelist
{
    /**
     * @var int|null
     */
    protected $whitelist_id;

    /**
     * @var int
     */
    protected $whitelist_type;

    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var DateTime|null
     */
    protected $active_until;

    /**
     * @param int           $whitelist_type
     * @param int           $routine_id
     * @param int           $ref_id
     * @param DateTime|null $active_until
     * @param int|null      $whitelist_id
     */
    public function __construct(
        int $whitelist_type,
        int $routine_id,
        int $ref_id,
        DateTime $active_until = null,
        int $whitelist_id = null
    ) {
        $this->whitelist_id = $whitelist_id;
        $this->whitelist_type = $whitelist_type;
        $this->routine_id = $routine_id;
        $this->ref_id = $ref_id;
        $this->active_until = $active_until;
    }

    /**
     * @inheritDoc
     */
    public function getWhitelistId() : ?int
    {
        return $this->whitelist_id;
    }

    /**
     * @inheritDoc
     */
    public function setWhitelistId(?int $whitelist_id) : IRoutineWhitelist
    {
        $this->whitelist_id = $whitelist_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getWhitelistType() : int
    {
        return $this->whitelist_type;
    }

    /**
     * @inheritDoc
     */
    public function setWhitelistType(int $type) : IRoutineWhitelist
    {
        $this->whitelist_type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId() : int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : IRoutineWhitelist
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(int $ref_id) : IRoutineWhitelist
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getActiveUntil() : DateTime
    {
        return $this->active_until;
    }

    /**
     * @inheritDoc
     */
    public function setActiveUntil(?DateTime $date) : IRoutineWhitelist
    {
        $this->active_until = $date;
        return $this;
    }
}