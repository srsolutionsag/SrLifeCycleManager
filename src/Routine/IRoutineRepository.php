<?php

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Rule\Rule;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;

/**
 * Interface IRoutineRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * As the routine object is the owning side of each (m:m) relation
 * this repository contains all operations regarding these relations.
 *
 * This means a rule cannot exist without a routine for example. The
 * rule is a standalone object but is useless without it being added
 * to a routine.
 */
interface IRoutineRepository
{
    /**
     * Returns a routine from the database for the given id.
     * Returns null if the given id does not exist.
     *
     * @param int  $routine_id
     * @return Routine
     */
    public function get(int $routine_id) : ?Routine;

    /**
     * Returns an empty routine DTO.
     *
     * Due to the many attributes of this DTO, this function
     * helps to instantiate an 'empty' DTO.
     *
     * @param int $origin_type
     * @param int $owner_id
     * @return Routine
     */
    public function getEmpty(int $origin_type, int $owner_id) : Routine;

    /**
     * Returns all existing routines from the database as DTOs.
     *
     * @return Routine[]
     */
    public function getAllAsDTO() : ?array;

    /**
     * Returns all existing routines from the database as array-data.
     * Always returns an array in order to work with ilTable2GUI.
     *
     * @return array
     */
    public function getAllAsArray() : array;

    /**
     * Returns all existing routines that affect the given ref-id.
     *
     * To retrieve routines as array-data true can be passed as
     * second argument.
     *
     * @param int  $ref_id
     * @param bool $as_array
     * @return Routine[]|array
     */
    public function getAllByScope(int $ref_id, bool $as_array = false) : array;

    /**
     * Returns all rules related to the given routine.
     * Always returns an array in order to work with ilTable2GUI.
     *
     * @param IRoutine $routine
     * @param bool     $as_array
     * @return Rule[]
     */
    public function getRules(IRoutine $routine, bool $as_array = false) : array;

    /**
     * Returns all notifications related to the given routine.
     * Always returns an array in order to work with ilTable2GUI.
     *
     * @param IRoutine $routine
     * @param bool     $as_array
     * @return Notification[]
     */
    public function getNotifications(IRoutine $routine, bool $as_array = false) : array;

    /**
     * Returns all whitelist entries for given routine.
     *
     * @param IRoutine $routine
     * @param bool     $as_array
     * @return IRoutineWhitelistEntry[]|null
     */
    public function getWhitelist(IRoutine $routine, bool $as_array) : ?array;

    /**
     * Stores a routine-rule relation between given routine and rule.
     *
     * @param IRoutine $routine
     * @param IRule    $rule
     * @return Routine
     */
    public function addRule(IRoutine $routine, IRule $rule) : Routine;

    /**
     * Stores a routine-notification relation between given routine and rule.
     *
     * @param IRoutine      $routine
     * @param INotification $notification
     * @return Routine
     */
    public function addNotification(IRoutine $routine, INotification $notification) : Routine;

    /**
     * Stores the given whitelist entry related to the given routine.
     *
     * @param IRoutine               $routine
     * @param IRoutineWhitelistEntry $entry
     * @return Routine
     */
    public function addWhitelistEntry(IRoutine $routine, IRoutineWhitelistEntry $entry) : Routine;

    /**
     * Creates or updates the given routine in the database.
     *
     * @param IRoutine $routine
     * @return Routine
     */
    public function store(IRoutine $routine) : Routine;

    /**
     * Removes a routine-rule relation between given routine and rule.
     *
     * @param IRoutine $routine
     * @param IRule    $rule
     * @return Routine
     */
    public function removeRule(IRoutine $routine, IRule $rule) : Routine;

    /**
     * Removes a routine-notification relation between given routine and notification.
     *
     * @param IRoutine      $routine
     * @param INotification $notification
     * @return Routine
     */
    public function removeNotification(IRoutine $routine, INotification $notification) : Routine;

    /**
     * Removes a routine whitelist entry from the given routine.
     *
     * @param IRoutine               $routine
     * @param IRoutineWhitelistEntry $entry
     * @return Routine
     */
    public function removeWhitelistEntry(IRoutine $routine, IRoutineWhitelistEntry $entry) : Routine;

    /**
     * Deletes an existing routine from the database.
     *
     * @param IRoutine $routine
     * @return bool
     */
    public function delete(IRoutine $routine) : bool;
}