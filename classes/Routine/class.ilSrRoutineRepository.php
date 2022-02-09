<?php

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;

/**
 * Class ilSrRoutineRepository
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ilSrRoutineRepository implements IRoutineRepository
{
    /**
     * @var ilTree
     */
    private $tree;

    /**
     * @var IRuleRepository
     */
    private $rule_repository;

    /**
     * ilSrRoutineRepository constructor.
     */
    public function __construct(IRuleRepository $rule_repository)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->rule_repository = $rule_repository;
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
        return (null !== $ar_routine) ?
            $this->transformToDTO($ar_routine) : null
        ;
    }

    /**
     * @inheritDoc
     */
    public function getEmpty(int $origin_type, int $owner_id) : Routine
    {
        return new Routine(
            null,
            '',
            0,
            false,
            $origin_type,
            $owner_id,
            new DateTime(),
            false,
            false,
            null
        );
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
        if (empty($ar_routines)) return null;

        $dto_array = [];
        foreach ($ar_routines as $ar_routine) {
            $dto_array[] = $this->transformToDTO($ar_routine);
        }

        return $dto_array;
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
        if (empty($ar_routines)) return [];

        $array = [];
        foreach ($ar_routines as $ar_routine) {
            $array[] = $this->transformToArray($ar_routine);
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function getAllByScope(int $ref_id, bool $as_array = false) : array
    {
        $parents = $this->getParentIdsRecursively($ref_id);
        // add current id to array as well.
        $parents[] = $ref_id;

        $routines = [];
        foreach ($parents as $parent_id) {
            // selects all routines which have the current
            // parent id stored.
            $ar_routines = ilSrRoutine::where([
                ilSrRoutine::F_REF_ID => $parent_id,
            ], '')->get();

            if (!empty($ar_routines)) {
                $parent_routines = [];
                foreach ($ar_routines as $ar_routine) {
                    if ($as_array) {
                        $parent_routines[] = $this->transformToArray($ar_routine);
                    } else {
                        $parent_routines[] = $this->transformToDTO($ar_routine);
                    }
                }

                // array_merge has to be used, as this function considers
                // duplicate array-entries. The heavy resource load can
                // be ignored as it wont deliver 1000+ results.
                $routines = array_merge($routines, $parent_routines);
            }
        }

        return (!empty($routines)) ? $routines : [];
    }

    /**
     * @inheritDoc
     */
    public function getRules(IRoutine $routine, bool $as_array = false) : array
    {
        // if no id is set the routine wasn't stored yet,
        // therefore no action required.
        if (null === $routine->getId()) return [];

        // fetches all rules that have a relation to the
        // provided routine id.
        $ar_rules = ilSrRule::leftjoin(
            ilSrRoutineRule::TABLE_NAME,
            ilSrRule::F_ID,
            ilSrRoutineRule::F_RULE_ID
        )->where([
            ilSrRoutineRule::F_ROUTINE_ID => $routine->getId(),
        ])->get();

        if (empty($ar_rules)) return [];

        $rules = [];
        foreach ($ar_rules as $ar_rule) {
            if ($as_array) {
                $rules[] = $this->rule_repository->transformToArray($ar_rule);
            } else {
                $rules[] = $this->rule_repository->transformToDTO($ar_rule);
            }
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getNotifications(IRoutine $routine, bool $as_array = false) : array
    {
        // TODO: Implement getNotifications() method.
    }

    /**
     * @inheritDoc
     */
    public function getWhitelist(IRoutine $routine, bool $as_array = false) : ?array
    {
        // TODO: Implement getWhitelist() method.
    }

    /**
     * @inheritDoc
     */
    public function addRule(IRoutine $routine, IRule $rule) : Routine
    {
        // TODO: Implement addRule() method.
    }

    /**
     * @inheritDoc
     */
    public function addNotification(IRoutine $routine, INotification $notification) : Routine
    {
        // TODO: Implement addNotification() method.
    }

    /**
     * @inheritDoc
     */
    public function addWhitelistEntry(
        IRoutine $routine,
        \srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry $entry
    ) : Routine {
        // TODO: Implement addWhitelistEntry() method.
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutine $routine) : Routine
    {
        if (null !== $routine->getId()) {
            $ar_routine = ilSrRoutine::find($routine->getId()) ?? new ilSrRoutine();
        } else {
            $ar_routine = new ilSrRoutine();
        }

        $ar_routine
            ->setId($routine->getId())
            ->setName($routine->getName())
            ->setRefId($routine->getRefId())
            ->setActive($routine->isActive())
            ->setOriginType($routine->getOriginType())
            ->setOwnerId($routine->getOwnerId())
            ->setCreationDate($routine->getCreationDate())
            ->setOptOutPossible($routine->isOptOutPossible())
            ->setElongationDays($routine->getElongationDays())
        ;

        $ar_routine->store();

        return $this->transformToDTO($ar_routine);
    }

    /**
     * @inheritDoc
     */
    public function removeRule(IRoutine $routine, IRule $rule) : Routine
    {
        // TODO: Implement removeRule() method.
    }

    /**
     * @inheritDoc
     */
    public function removeNotification(IRoutine $routine, INotification $notification) : Routine
    {
        // TODO: Implement removeNotification() method.
    }

    /**
     * @inheritDoc
     */
    public function removeWhitelistEntry(
        IRoutine $routine,
        \srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry $entry
    ) : Routine {
        // TODO: Implement removeWhitelistEntry() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutine $routine) : bool
    {
        // if no id is set the routine wasn't stored yet,
        // therefore no action required.
        if (null === $routine->getId()) return true;

        // abort if the given routine was not found in
        // the database.
        $ar_routine = ilSrRoutine::find($routine->getId());
        if (null === $ar_routine) return false;

        // iterate through every relation type.
        // this needs to be done, as ilDB does not yet
        // support constraints.
        foreach ($this->getRelations($routine) as $relations_of_type) {
            // if there are relations of the current type,
            // delete them all.
            if (!empty($relations_of_type)) {
                foreach ($relations_of_type as $relation) {
                    $relation->delete();
                }
            }
        }

        // finally delete the routine itself and return.
        $ar_routine->delete();
        return true;
    }

    /**
     * Returns all relations between a given routine and all
     * intermediate tables (m:m).
     *
     * Note that this method returns an array structured as:
     *
     *      array(
     *          IRule => [...],
     *          INotification => [...],
     *          ...
     *      );
     *
     * @param IRoutine $routine
     * @return array
     */
    private function getRelations(IRoutine $routine) : array
    {
        $relations = [];

        $relations[IRule::class] = ilSrRoutineRule::where([
            ilSrRoutineRule::F_ROUTINE_ID => $routine->getId(),
        ])->get();

        $relations[INotification::class] = ilSrRoutineNotification::where([
            ilSrRoutineNotification::F_ROUTINE_ID => $routine->getId(),
        ])->get();

        $relations[IRoutineWhitelistEntry::class] = ilSrRoutineWhitelistEntry::where([
            ilSrRoutineWhitelistEntry::F_ROUTINE_ID => $routine->getId(),
        ])->get();

        return $relations;
    }

    /**
     * Returns all parent ids for given ref-id recursively.
     *
     * @param int $ref_id
     * @return array|null
     */
    private function getParentIdsRecursively(int $ref_id) : ?array
    {
        static $parents;

        $parent_id = $this->tree->getParentId($ref_id);
        // type-cast is not redundant, as getParentId() returns
        // a string (not as stated by the phpdoc).
        if (null !== $parent_id && 0 < (int) $parent_id) {
            $parents[] = (int) $parent_id;
            $this->getParentIdsRecursively($parent_id);
        }

        return (!empty($parents)) ? $parents : null;
    }

    /**
     * transforms an ActiveRecord instance into a DTO.
     *
     * @param ilSrRoutine $ar_routine
     * @return Routine
     */
    private function transformToDTO(ilSrRoutine $ar_routine) : Routine
    {
        return new Routine(
            $ar_routine->getId(),
            $ar_routine->getName(),
            $ar_routine->getRefId(),
            $ar_routine->isActive(),
            $ar_routine->getOriginType(),
            $ar_routine->getOwnerId(),
            $ar_routine->getCreationDate(),
            $ar_routine->isOptOutPossible(),
            $ar_routine->getElongationDays()
        );
    }

    /**
     * transforms an ActiveRecord instance into array-data.
     * (primarily used for TableGUI's)
     *
     * @param ilSrRoutine $ar_routine
     * @return array
     */
    private function transformToArray(ilSrRoutine $ar_routine) : array
    {
        return [
            ilSrRoutine::F_ID                   => $ar_routine->getId(),
            ilSrRoutine::F_NAME                 => $ar_routine->getName(),
            ilSrRoutine::F_REF_ID               => $ar_routine->getRefId(),
            ilSrRoutine::F_ACTIVE               => $ar_routine->isActive(),
            ilSrRoutine::F_ORIGIN_TYPE          => $ar_routine->getOriginType(),
            ilSrRoutine::F_OWNER_ID             => $ar_routine->getOwnerId(),
            ilSrRoutine::F_CREATION_DATE        => $ar_routine->getCreationDate(),
            ilSrRoutine::F_OPT_OUT_POSSIBLE     => $ar_routine->isOptOutPossible(),
            ilSrRoutine::F_ELONGATION_DAYS      => $ar_routine->getElongationDays(),
        ];
    }
}