<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTime;
use DateInterval;

/**
 * Routine DTO
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Routine implements IRoutine
{
    /**
     * @var int|null
     */
    protected $routine_id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var int
     */
    protected $origin_type;

    /**
     * @var int
     */
    protected $owner_id;

    /**
     * @var DateTime
     */
    protected $creation_date;

    /**
     * @var bool
     */
    protected $opt_out_possible;

    /**
     * @var int|null
     */
    protected $elongation_days;

    /**
     * @var string[]
     */
    protected $execution_dates = [];

    /**
     * @param string   $name
     * @param int      $ref_id
     * @param bool     $is_active
     * @param int      $origin_type
     * @param int      $owner_id
     * @param DateTime $creation_date
     * @param bool     $is_opt_out_possible
     * @param string[] $execution_dates
     * @param int|null $elongation_days
     * @param int|null $routine_id
     */
    public function __construct(
        string $name,
        int $ref_id,
        bool $is_active,
        int $origin_type,
        int $owner_id,
        DateTime $creation_date,
        bool $is_opt_out_possible,
        array $execution_dates,
        int $elongation_days = null,
        int $routine_id = null
    ) {
        $this->routine_id = $routine_id;
        $this->name = $name;
        $this->ref_id = $ref_id;
        $this->active = $is_active;
        $this->origin_type = $origin_type;
        $this->owner_id = $owner_id;
        $this->creation_date = $creation_date;
        $this->execution_dates = $execution_dates;
        $this->opt_out_possible = $is_opt_out_possible;
        $this->elongation_days = $elongation_days;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId() : ?int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : IRoutine
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name) : IRoutine
    {
        $this->name = $name;
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
    public function setRefId(int $ref_id) : IRoutine
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $is_active) : IRoutine
    {
        $this->active = $is_active;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getElongationDays() : ?int
    {
        return $this->elongation_days;
    }

    /**
     * @inheritDoc
     */
    public function setElongationDays(?int $days) : IRoutine
    {
        $this->elongation_days = $days;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isOptOutPossible() : bool
    {
        return $this->opt_out_possible;
    }

    /**
     * @inheritDoc
     */
    public function setOptOutPossible(bool $is_possible) : IRoutine
    {
        $this->opt_out_possible = $is_possible;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOriginType() : int
    {
        return $this->origin_type;
    }

    /**
     * @inheritDoc
     */
    public function setOriginType(int $type) : IRoutine
    {
        $this->origin_type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    /**
     * @inheritDoc
     */
    public function setOwnerId(int $owner_id) : IRoutine
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreationDate() : DateTime
    {
        return $this->creation_date;
    }

    /**
     * @inheritDoc
     */
    public function setCreationDate(DateTime $date) : IRoutine
    {
        $this->creation_date = $date;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExecutionDates() : array
    {
        return $this->execution_dates;
    }

    /**
     * @inheritDoc
     */
    public function setExecutionDates(array $dates) : IRoutine
    {
        $this->execution_dates = $dates;
        return $this;
    }
}