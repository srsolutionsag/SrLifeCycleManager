<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * IRoutineAwareNotification describes the DTO of a notification.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Other than the notification DAO, the notification DTO should also
 * contain methods that regard the relationship to a routine.
 *
 * This way CRUD operations will be easier because only an instance of
 * this interface must be expected by the repository in order to manage
 * a notifications relations too.
 */
interface IRoutineAwareNotification extends INotification
{
    /**
     * @return int
     */
    public function getRelationId() : int;

    /**
     * @param int $relation_id
     * @return IRoutineAwareNotification
     */
    public function setRelationId(int $relation_id) : IRoutineAwareNotification;

    /**
     * @return int
     */
    public function getRoutineId() : int;

    /**
     * @param int $routine_id
     * @return IRoutineAwareNotification
     */
    public function setRoutineId(int $routine_id) : IRoutineAwareNotification;

    /**
     * @return int
     */
    public function getDaysBeforeSubmission() : int;

    /**
     * @param int $days
     * @return IRoutineAwareNotification
     */
    public function setDaysBeforeSubmission(int $days) : IRoutineAwareNotification;
}