<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineNotification
{
    /**
     * IRoutineNotification attribute names
     */
    public const F_ID                       = 'id';
    public const F_ROUTINE_ID               = 'routine_id';
    public const F_NOTIFICATION_ID          = 'notification_id';
    public const F_DAYS_BEFORE_SUBMISSION   = 'days_before_submission';

    /**
     * @return int|null
     */
    public function getId() : ?int;

    /**
     * @param int $id
     * @return IRoutineNotification
     */
    public function setId(int $id) : IRoutineNotification;

    /**
     * @return int|null
     */
    public function getRoutineId() : ?int;

    /**
     * @param int $routine_id
     * @return IRoutineNotification
     */
    public function setRoutineId(int $routine_id) : IRoutineNotification;

    /**
     * @return int|null
     */
    public function getNotificationId() : ?int;

    /**
     * @param int $notification_id
     * @return IRoutineNotification
     */
    public function setNotificationId(int $notification_id) : IRoutineNotification;

    /**
     * @return int
     */
    public function getDaysBeforeSubmission() : int;

    /**
     * @param int $days
     * @return IRoutineNotification
     */
    public function setDaysBeforeSubmission(int $days) : IRoutineNotification;
}