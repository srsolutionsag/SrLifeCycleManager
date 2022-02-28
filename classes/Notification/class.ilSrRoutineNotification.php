<?php // strict types are not possible with ActiveRecord.

use srag\Plugins\_SrLifeCycleManager\Notification\IRoutineNotificationRelation;

/**
 * Routine-Notification relationship DAO.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineNotification extends ActiveRecord implements IRoutineNotificationRelation
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine_msg';

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
    protected $relation_id;

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
    public function getRelationId() : ?int
    {
        return $this->relation_id;
    }

    /**
     * @inheritDoc
     */
    public function setRelationId(?int $relation_id) : IRoutineNotificationRelation
    {
        $this->relation_id = $relation_id;
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
    public function setRoutineId(?int $routine_id) : IRoutineNotificationRelation
    {
        $this->routine_id = $routine_id;
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
    public function setNotificationId(?int $notification_id) : IRoutineNotificationRelation
    {
        $this->notification_id = $notification_id;
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
    public function setDaysBeforeSubmission(int $days) : IRoutineNotificationRelation
    {
        $this->days_before_submission = $days;
        return $this;
    }
}