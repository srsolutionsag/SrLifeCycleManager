<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WhitelistEntry implements IWhitelistEntry
{
    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var bool
     */
    protected $is_opt_out;

    /**
     * @var int|null
     */
    protected $elongation;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @param int      $routine_id
     * @param int      $ref_id
     * @param bool     $is_opt_out
     * @param DateTime $date
     * @param int|null $elongation
     */
    public function __construct(
        int $routine_id,
        int $ref_id,
        bool $is_opt_out,
        DateTime $date,
        int $elongation = null
    ) {
        $this->routine_id = $routine_id;
        $this->ref_id = $ref_id;
        $this->is_opt_out = $is_opt_out;
        $this->elongation = $elongation;
        $this->date = $date;
    }

    /**
     * @inheritdoc
     */
    public function getRoutineId() : int
    {
        return $this->routine_id;
    }

    /**
     * @inheritdoc
     */
    public function setRoutineId(int $routine_id) : IWhitelistEntry
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * @inheritdoc
     */
    public function setRefId(int $ref_id) : IWhitelistEntry
    {
        $this->ref_id = $ref_id;
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function isOptOut() : bool
    {
        return $this->is_opt_out;
    }

    /**
     * @inheritdoc
     */
    public function setOptOut(bool $is_opt_out) : IWhitelistEntry
    {
        $this->is_opt_out = $is_opt_out;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getElongation() : ?int
    {
        return $this->elongation;
    }

    /**
     * @inheritdoc
     */
    public function setElongation(int $elongation) : IWhitelistEntry
    {
        $this->elongation = $elongation;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }

    /**
     * @inheritdoc 
     */
    public function setDate(DateTime $date) : IWhitelistEntry
    {
        $this->date = $date;
        return $this;
    }
}