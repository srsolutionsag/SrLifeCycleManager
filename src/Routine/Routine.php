<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
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
    protected $ref_id;

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
    protected $active;

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
     * @var IRule[]
     */
    protected $rules;

    /**
     * @var INotification[]
     */
    protected $notifications;

    /**
     * @param int      $ref_id
     * @param int      $owner_id
     * @param string   $routine_type
     * @param int      $origin
     * @param string   $title
     * @param bool     $active
     * @param bool     $has_opt_out
     * @param DateTime $creation_date
     * @param int|null $elongation
     * @param int|null $routine_id
     * @param IRule[]  $rules
     * @param array    $notifications
     */
    public function __construct(
        int $ref_id,
        int $owner_id,
        string $routine_type,
        int $origin,
        string $title,
        bool $active,
        bool $has_opt_out,
        DateTime $creation_date,
        int $elongation = null,
        int $routine_id = null,
        array $rules = [],
        array $notifications = []
    ) {
        $this->routine_id = $routine_id;
        $this->ref_id = $ref_id;
        $this->owner_id = $owner_id;
        $this->routine_type = $routine_type;
        $this->origin = $origin;
        $this->title = $title;
        $this->active = $active;
        $this->has_opt_out = $has_opt_out;
        $this->elongation = $elongation;
        $this->creation_date = $creation_date;
        $this->rules = $rules;
        $this->notifications = $notifications;
    }

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int
    {
        return $this->routine_id;
    }

    /**
     * @param int $routine_id
     * @return IRoutine
     */
    public function setRoutineId(int $routine_id) : IRoutine
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * @param int $ref_id
     * @return IRoutine
     */
    public function setRefId(int $ref_id) : IRoutine
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    /**
     * @param int $owner_id
     * @return IRoutine
     */
    public function setOwnerId(int $owner_id) : IRoutine
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrigin() : int
    {
        return $this->origin;
    }

    /**
     * @param int $origin
     * @return Routine
     */
    public function setOrigin(int $origin) : IRoutine
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoutineType() : string
    {
        return $this->routine_type;
    }

    /**
     * @param string $routine_type
     * @return IRoutine
     */
    public function setRoutineType(string $routine_type) : IRoutine
    {
        $this->routine_type = $routine_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return IRoutine
     */
    public function setTitle(string $title) : IRoutine
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @param bool $is_active
     * @return IRoutine
     */
    public function setActive(bool $is_active) : IRoutine
    {
        $this->active = $is_active;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOptOut() : bool
    {
        return $this->has_opt_out;
    }

    /**
     * @param bool $has_opt_out
     * @return IRoutine
     */
    public function setOptOut(bool $has_opt_out) : IRoutine
    {
        $this->has_opt_out = $has_opt_out;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getElongation() : ?int
    {
        return $this->elongation;
    }

    /**
     * @param int|null $elongation
     * @return IRoutine
     */
    public function setElongation(?int $elongation) : IRoutine
    {
        $this->elongation = $elongation;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate() : DateTime
    {
        return $this->creation_date;
    }

    /**
     * @param DateTime $creation_date
     * @return IRoutine
     */
    public function setCreationDate(DateTime $creation_date) : IRoutine
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    /**
     * @return IRule[]
     */
    public function getRules() : array
    {
        return $this->rules;
    }

    /**
     * @param IRule[] $rules
     * @return IRoutine
     */
    public function setRules(array $rules) : IRoutine
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @return INotification[]
     */
    public function getNotifications() : array
    {
        return $this->notifications;
    }

    /**
     * @param INotification[] $notifications
     * @return IRoutine
     */
    public function setNotifications(array $notifications) : IRoutine
    {
        $this->notifications = $notifications;
        return $this;
    }
}