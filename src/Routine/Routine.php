<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;

/**
 * Class Routine (DTO)
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class Routine implements IRoutine
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int
     */
    private $ref_id;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var int
     */
    private $origin_type;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @var \DateTime
     */
    private $creation_date;

    /**
     * @var bool
     */
    private $opt_out_possible;

    /**
     * @var bool
     */
    private $elongation_possible;

    /**
     * @var int|null
     */
    private $elongation_days;

    /**
     * @var IRule[]
     */
    private $rules;

    /**
     * @var INotification[]
     */
    private $notifications;

    /**
     * @var IRoutineWhitelistEntry[]
     */
    private $whitelist;

    /**
     * Routine constructor
     *
     * @param int|null   $id
     * @param int        $ref_id
     * @param bool       $is_active
     * @param int        $origin_type
     * @param int        $owner_id
     * @param \DateTime  $creation_date
     * @param bool       $is_opt_out_possible
     * @param bool       $is_elongation_possible
     * @param int|null   $elongation_days
     * @param array|null $rules
     * @param array|null $notifications
     * @param array|null $whitelist
     */
    public function __construct(
        ?int $id,
        int $ref_id,
        bool $is_active,
        int $origin_type,
        int $owner_id,
        \DateTime $creation_date,
        bool $is_opt_out_possible,
        bool $is_elongation_possible,
        int $elongation_days = null,
        array $rules = null,
        array $notifications = null,
        array $whitelist = null
    ) {
        $this->id                   = $id;
        $this->ref_id               = $ref_id;
        $this->active               = $is_active;
        $this->origin_type          = $origin_type;
        $this->owner_id             = $owner_id;
        $this->creation_date        = $creation_date;
        $this->opt_out_possible     = $is_opt_out_possible;
        $this->elongation_possible  = $is_elongation_possible;
        $this->elongation_days      = $elongation_days;

        $this->rules = $rules ?? [];
        $this->notifications = $notifications ?? [];
        $this->whitelist = $whitelist ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(int $id) : IRoutine
    {
        $this->id = $id;
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
    public function setActive(bool $is_is_active) : IRoutine
    {
        $this->active = $is_is_active;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isElongationPossible() : bool
    {
        return $this->elongation_possible;
    }

    /**
     * @inheritDoc
     */
    public function setElongationPossible(bool $is_possible) : IRoutine
    {
        $this->elongation_possible = $is_possible;
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
    public function setElongationDays(int $days) : IRoutine
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
    public function getCreationDate() : \DateTime
    {
        return $this->creation_date;
    }

    /**
     * @inheritDoc
     */
    public function setCreationDate(\DateTime $date) : IRoutine
    {
        $this->creation_date = $date;
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
    public function addRules(array $rules) : IRoutine
    {
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $this->rules[] = $rule;
            }
        }

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
    public function addNotifications(array $notifications) : IRoutine
    {
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                $this->notifications[] = $notification;
            }
        }

        return $this;
    }

    /**
     * @return IRoutineWhitelistEntry[]
     */
    public function getWhitelist() : array
    {
        return $this->whitelist;
    }

    /**
     * @param IRoutineWhitelistEntry[] $entries
     * @return IRoutine
     */
    public function addWhitelistEntries(array $entries) : IRoutine
    {
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $this->whitelist[] = $entry;
            }
        }

        return $this;
    }
}