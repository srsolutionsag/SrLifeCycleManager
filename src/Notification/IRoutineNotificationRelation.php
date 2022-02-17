<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * IRoutineNotificationRelation describes the relationship between
 * a routine and a notification.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineNotificationRelation
{
    public const F_RELATION_ID = 'relation_id';
    public const F_ROUTINE_ID = 'routine_id';
    public const F_NOTIFICATION_ID = 'notification_id';
    public const F_DAYS_BEFORE_SUBMISSION = 'days_before_submission';

    /**
     * @return int|null
     */
    public function getRelationId() : ?int;

    /**
     * @param int $relation_id
     * @return IRoutineNotificationRelation
     */
    public function setRelationId(int $relation_id) : IRoutineNotificationRelation;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineNotificationRelation
     */
    public function setRoutineId(int $routine_id) : IRoutineNotificationRelation;

    /**
     * @return int|null
     */
    public function getNotificationId() : ?int;

    /**
     * @param int $notification_id
     * @return IRoutineNotificationRelation
     */
    public function setNotificationId(int $notification_id) : IRoutineNotificationRelation;

    /**
     * @return int
     */
    public function getDaysBeforeSubmission() : int;

    /**
     * @param int $days
     * @return IRoutineNotificationRelation
     */
    public function setDaysBeforeSubmission(int $days) : IRoutineNotificationRelation;
}