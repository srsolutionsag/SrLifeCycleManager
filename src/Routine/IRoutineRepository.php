<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;

/**
 * Interface IRoutineRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface IRoutineRepository
{
    /**
     * @param int $routine_id
     * @return Routine
     */
    public function get(int $routine_id) : ?Routine;

    /**
     * @param int $origin_type
     * @param int $owner_id
     * @return Routine
     */
    public function getEmptyDTO(int $origin_type, int $owner_id) : Routine;

    /**
     * @param IRoutine $routine
     * @return Routine
     */
    public function store(IRoutine $routine) : Routine;

    /**
     * @return Routine[]
     */
    public function getAllAsDTO() : ?array;

    /**
     * @return array
     */
    public function getAllAsArray() : array;

    /**
     * @param IRoutine $routine
     * @param IRule    $rule
     * @return Routine
     */
    public function storeRuleRelation(IRoutine $routine, IRule $rule) : Routine;

    /**
     * @param IRoutine      $routine
     * @param INotification $notification
     * @return Routine
     */
    public function storeNotificationRelation(IRoutine $routine, INotification $notification) : Routine;

    /**
     * @param IRoutine               $routine
     * @param IRoutineWhitelistEntry $entry
     * @return Routine
     */
    public function storeWhitelistEntry(IRoutine $routine, IRoutineWhitelistEntry $entry) : Routine;

    /**
     * @param IRoutine $routine
     * @return bool
     */
    public function delete(IRoutine $routine) : bool;

    /**
     * @param IRoutine $routine
     * @param IRule    $rule
     * @return bool
     */
    public function deleteRuleRelation(IRoutine $routine, IRule $rule) : bool;

    /**
     * @param IRoutine      $routine
     * @param INotification $notification
     * @return bool
     */
    public function deleteNotificationRelation(IRoutine $routine, INotification $notification) : bool;

    /**
     * @param IRoutine               $routine
     * @param IRoutineWhitelistEntry $entry
     * @return bool
     */
    public function deleteWhitelistEntry(IRoutine $routine, IRoutineWhitelistEntry $entry) : bool;
}