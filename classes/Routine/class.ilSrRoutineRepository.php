<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * Class ilSrRoutineRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrRoutineRepository implements IRoutineRepository
{
    /**
     * @param int $routine_id
     * @return IRule[]
     */
    public function getRulesByRoutineId(int $routine_id) : array
    {
        $ar_rules = ilSrRoutineRule::leftjoin(
            ilSrRule::TABLE_NAME,
            ilSrRoutineRule::F_RULE_ID,
            ilSrRule::F_ID
        )->where([
            ilSrRoutineRule::F_ROUTINE_ID => $routine_id
        ], '='
        )->get();


    }

    /**
     * @param int $routine_id
     * @return INotification[]
     */
    public function getNotificationsByRoutineId(int $routine_id) : array
    {

    }

    /**
     * @param int $routine_id
     * @return IRoutineWhitelistEntry[]
     */
    public function getWhitelistByRoutineId(int $routine_id) : array
    {

    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id) : ?Routine
    {
        /**
         * @var $ar_routine ilSrRoutine|null
         */
        $ar_routine = ilSrRoutine::find($routine_id);
        if (null !== $ar_routine) {
            return new Routine(
                $ar_routine->getId(),
                $ar_routine->getRefId(),
                $ar_routine->isActive(),
                $ar_routine->getOriginType(),
                $ar_routine->getOwnerId(),
                $ar_routine->getCreationDate(),
                $ar_routine->isOptOutPossible(),
                $ar_routine->isElongationPossible(),
                $ar_routine->getElongationDays(),
                $this->getRulesByRoutineId($routine_id),
                $this->getNotificationsByRoutineId($routine_id),
                $this->getWhitelistByRoutineId($routine_id)
            );
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutine $routine) : Routine
    {
        $ar_routine = (null !== $routine->getId()) ?
            (ilSrRoutine::find($routine->getId()) ?? new ilSrRoutine()) :
            new ilSrRoutine()
        ;

        $ar_routine
            ->setId($routine->getId())
            ->setRefId($routine->getRefId())
            ->setActive($routine->isActive())
            ->setOriginType($routine->getOriginType())
            ->setOwnerId($routine->getOwnerId())
            ->setCreationDate($routine->getCreationDate())
            ->setOptOutPossible($routine->isOptOutPossible())
            ->setElongationPossible($routine->isElongationPossible())
            ->setElongationDays($routine->getElongationDays())
        ;

        $ar_routine->store();

        return $this->transformToDTO($ar_routine);
    }

    /**
     * @inheritDoc
     */
    public function getAllAsDTO() : ?array
    {
        /**
         * @var $ar_routines ilSrRoutine[]
         */
        $ar_routines = ilSrRoutine::get();
        if (!empty($ar_routines)) {
            $dto_array = [];
            foreach ($ar_routines as $ar_routine) {
                $dto_array[] = $this->transformToDTO($ar_routine);
            }

            return $dto_array;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAllAsArray() : array
    {
        /**
         * @var $ar_routines ilSrRoutine[]
         */
        $ar_routines = ilSrRoutine::get();
        if (!empty($ar_routines)) {
            $array = [];
            foreach ($ar_routines as $ar_routine) {
                $array[] = $this->transformToArray($ar_routine);
            }

            return $array;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function storeRuleRelation(IRoutine $routine, IRule $rule) : Routine
    {
        $relation = ilSrRoutineRule::where([
            ilSrRoutineRule::F_ROUTINE_ID => $routine->getId(),
            ilSrRoutineRule::F_RULE_ID    => $rule->getId(),
        ], '=')->first();

        // ActiveRecord::first() returns null if results are empty
        if (null === $relation) {
            $relation = new ilSrRoutineRule();
            $relation
                ->setRoutineId($routine->getId())
                ->setRuleId($rule->getId())
            ;

            $relation->store();
        }


    }

    /**
     * @inheritDoc
     */
    public function storeNotificationRelation(
        IRoutine $routine,
        INotification $notification
    ) : Routine {
        // TODO: Implement storeNotificationRelation() method.
    }

    /**
     * @inheritDoc
     */
    public function storeWhitelistEntry(
        IRoutine $routine,
        IRoutineWhitelistEntry $entry
    ) : Routine {
        // TODO: Implement storeWhitelistEntry() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutine $routine) : bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteRuleRelation(
        IRoutine $routine,
        IRule $rule
    ) : bool {
        // TODO: Implement deleteRuleRelation() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteNotificationRelation(
        IRoutine $routine,
        INotification $notification
    ) : bool {
        // TODO: Implement deleteNotificationRelation() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteWhitelistEntry(
        IRoutine $routine,
        IRoutineWhitelistEntry $entry
    ) : bool {
        // TODO: Implement deleteWhitelistEntry() method.
    }

    /**
     * transforms an ActiveRecord instance into a DTO.
     *
     * @param IRoutine $ar_routine
     * @return Routine
     */
    private function transformToDTO(IRoutine $ar_routine) : Routine
    {
        return new Routine(
            $ar_routine->getId(),
            $ar_routine->getRefId(),
            $ar_routine->isActive(),
            $ar_routine->getOriginType(),
            $ar_routine->getOwnerId(),
            $ar_routine->getCreationDate(),
            $ar_routine->isOptOutPossible(),
            $ar_routine->isElongationPossible(),
            $ar_routine->getElongationDays()
        );
    }

    /**
     * transforms an ActiveRecord instance into an array (primarily used for TableGUI's).
     *
     * @param IRoutine $ar_routine
     * @return array
     */
    private function transformToArray(IRoutine $ar_routine) : array
    {
        return [
            ilSrRoutine::F_ID                   => $ar_routine->getId(),
            ilSrRoutine::F_REF_ID               => $ar_routine->getRefId(),
            ilSrRoutine::F_ACTIVE               => $ar_routine->isActive(),
            ilSrRoutine::F_ORIGIN_TYPE          => $ar_routine->getOriginType(),
            ilSrRoutine::F_OWNER_ID             => $ar_routine->getOwnerId(),
            ilSrRoutine::F_CREATION_DATE        => $ar_routine->getCreationDate(),
            ilSrRoutine::F_OPT_OUT_POSSIBLE     => $ar_routine->isOptOutPossible(),
            ilSrRoutine::F_ELONGATION_POSSIBLE  => $ar_routine->isElongationPossible(),
            ilSrRoutine::F_ELONGATION_DAYS      => $ar_routine->getElongationDays(),
        ];
    }
}