<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

/**
 * Interface IRoutineNotification defines how a routine-rule relation must look like.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRoutineNotification
{
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