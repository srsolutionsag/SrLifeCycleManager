<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use DateTime;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Routine implements IRoutine
{
    /**
     * @var int|null
     */
    protected $routine_id;

    /**
     * @var int
     */
    protected $owner_id;

    /**
     * @var string
     */
    protected $routine_type;

    /**
     * @var int
     */
    protected $origin;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $has_opt_out;

    /**
     * @var int|null
     */
    protected $elongation;

    /**
     * @var DateTime
     */
    protected $creation_date;

    /**
     * @param int      $owner_id
     * @param string   $routine_type
     * @param int      $origin
     * @param string   $title
     * @param bool     $has_opt_out
     * @param DateTime $creation_date
     * @param int|null $elongation
     * @param int|null $routine_id
     */
    public function __construct(
        int $owner_id,
        string $routine_type,
        int $origin,
        string $title,
        bool $has_opt_out,
        DateTime $creation_date,
        int $elongation = null,
        int $routine_id = null
    ) {
        $this->routine_id = $routine_id;
        $this->owner_id = $owner_id;
        $this->routine_type = $routine_type;
        $this->origin = $origin;
        $this->title = $title;
        $this->has_opt_out = $has_opt_out;
        $this->elongation = $elongation;
        $this->creation_date = $creation_date;
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
    public function getOrigin() : int
    {
        return $this->origin;
    }

    /**
     * @inheritDoc
     */
    public function setOrigin(int $origin) : IRoutine
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineType() : string
    {
        return $this->routine_type;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineType(string $routine_type) : IRoutine
    {
        $this->routine_type = $routine_type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title) : IRoutine
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasOptOut() : bool
    {
        return $this->has_opt_out;
    }

    /**
     * @inheritDoc
     */
    public function setOptOut(bool $has_opt_out) : IRoutine
    {
        $this->has_opt_out = $has_opt_out;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getElongation() : ?int
    {
        return $this->elongation;
    }

    /**
     * @inheritDoc
     */
    public function setElongation(?int $amount) : IRoutine
    {
        $this->elongation = $amount;
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
    public function setCreationDate(DateTime $creation_date) : IRoutine
    {
        $this->creation_date = $creation_date;
        return $this;
    }
}