<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineNotification;

/**
 * Class ilSrRoutineNotification is responsible for storing routine-rule (m:m) relations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineNotification extends ActiveRecord implements IRoutineNotification
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine_msg';

    /**
     * ilSrRoutineNotification attribute names
     */
    public const F_ID                       = 'id';
    public const F_ROUTINE_ID               = 'routine_id';
    public const F_NOTIFICATION_ID          = 'rule_id';
    public const F_DAYS_BEFORE_SUBMISSION   = 'days_before_submission';

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_is_primary  true
     * @con_is_notnull  true
     * @con_sequence    true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $id;

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $routine_id;

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $notification_id;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $days_before_submission;

    /**
     * @inheritDoc
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
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
    public function setId(?int $id) : IRoutineNotification
    {
        $this->id = $id;
        return $this;
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
    public function setRoutineId(?int $routine_routine_id) : IRoutineNotification
    {
        $this->routine_id = $routine_routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationId() : ?int
    {
        return $this->notification_id;
    }

    /**
     * @inheritDoc
     */
    public function setNotificationId(?int $notification_notification_id) : IRoutineNotification
    {
        $this->notification_id = $notification_notification_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDaysBeforeSubmission() : int
    {
        return $this->days_before_submission;
    }

    /**
     * @inheritDoc
     */
    public function setDaysBeforeSubmission(int $days) : IRoutineNotification
    {
        $this->days_before_submission = $days;
        return $this;
    }
}