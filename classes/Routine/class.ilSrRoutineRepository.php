<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\Routine;
use srag\Plugins\SrLifeCycleManager\Rule\IRule;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelist;
use srag\Plugins\SrLifeCycleManager\Rule\IRoutineRuleRelation;
use srag\Plugins\SrLifeCycleManager\Notification\IRoutineNotificationRelation;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineRepository implements IRoutineRepository
{
    /**
     * @var IRoutineWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @param IRoutineWhitelistRepository $whitelist_repository
     * @param ilDBInterface               $database
     * @param ilTree                      $tree
     */
    public function __construct(
        IRoutineWhitelistRepository $whitelist_repository,
        ilDBInterface $database,
        ilTree $tree
    ) {
        $this->whitelist_repository = $whitelist_repository;
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function whitelist() : IRoutineWhitelistRepository
    {
        return $this->whitelist_repository;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id) : ?IRoutine
    {
        /** @var $ar_routine ilSrRoutine|null */
        $ar_routine = ilSrRoutine::find($routine_id);
        return (null !== $ar_routine) ?
            $this->transformToDTO($ar_routine) : null
        ;
    }

    /**
     * @inheritDoc
     */
    public function getAll(bool $array_data = false) : array
    {
        if ($array_data) {
            return $this->getAllAsArray();
        }

        return $this->getAllAsDTO();
    }

    /**
     * @inheritDoc
     */
    public function getAllByScope(int $ref_id, bool $array_data = false) : array
    {
        // gather all parent objects of the given ref-id and
        // add the id itself to the array as well.
        $parents = $this->getParentIdsRecursively($ref_id);
        $parents[] = $ref_id;

        $routine_table = ilSrRoutine::TABLE_NAME;
        $in_group = implode(',', $parents);

        $query = "
            SELECT * FROM $routine_table AS routine WHERE routine.ref_id IN ($in_group);
        ";

        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        if ($array_data) {
            return $results;
        }

        $routines = [];
        foreach ($results as $result) {
            $routines[] = new Routine(
                $result[IRoutine::F_NAME],
                $result[IRoutine::F_REF_ID],
                (bool) $result[IRoutine::F_ACTIVE],
                $result[IRoutine::F_ORIGIN_TYPE],
                $result[IRoutine::F_OWNER_ID],
                DateTime::createFromFormat(ilSrRoutine::MYSQL_DATE_FORMAT, $result[IRoutine::F_CREATION_DATE]),
                (bool) $result[IRoutine::F_OPT_OUT_POSSIBLE],
                explode(',', $result[IRoutine::F_EXECUTIONS_DATES]),
                $result[IRoutine::F_ELONGATION_DAYS],
                $result[IRoutine::F_ROUTINE_ID]
            );
        }

        return $routines;
    }

    /**
     * @inheritDoc
     */
    public function getEmpty(int $origin_type, int $owner_id) : IRoutine
    {
        return new Routine(
            '',
            0,
            false,
            $origin_type,
            $owner_id,
            new DateTime(),
            false,
            []
        );
    }


    /**
     * @inheritDoc
     */
    public function getNextExecutionDate(IRoutine $routine) : ?DateTime
    {
        $today = new DateTime();
        $closest = null;

        foreach ($routine->getExecutionDates() as $date) {
            // build the execution date from the given string and stored format.
            $exec_date = DateTime::createFromFormat(IRoutine::EXECUTION_DATES_FORMAT, $date);

            // if the execution date is already in the past, the date will be
            // added 1 year.
            if ($today > $exec_date) {
                $exec_date->add(new DateInterval("P1Y"));
            }

            // set the closest date if:
            //      (a) the exec date is after today and closest is null, or
            //      (b) the exec date is after today BUT before the closest.
            if ((null === $closest && $today < $exec_date) ||
                (null !== $closest && $today < $exec_date && $closest > $exec_date)
            ) {
                $closest = $exec_date;
            }
        }

        return $closest;
    }

    /**
     * @inheritDoc
     */
    public function store(IRoutine $routine) : IRoutine
    {
        $ar_routine = (null !== $routine->getRoutineId()) ?
            (ilSrRoutine::find($routine->getRoutineId()) ?? new ilSrRoutine()):
            new ilSrRoutine()
        ;

        $ar_routine
            ->setRoutineId($routine->getRoutineId())
            ->setName($routine->getName())
            ->setRefId($routine->getRefId())
            ->setActive($routine->isActive())
            ->setOriginType($routine->getOriginType())
            ->setOwnerId($routine->getOwnerId())
            ->setCreationDate($routine->getCreationDate())
            ->setOptOutPossible($routine->isOptOutPossible())
            ->setElongationDays($routine->getElongationDays())
            ->setExecutionDates($routine->getExecutionDates())
            ->store()
        ;

        return $this->transformToDTO($ar_routine);
    }

    /**
     * @inheritDoc
     */
    public function delete(IRoutine $routine) : bool
    {
        // if no id is set the routine wasn't stored yet,
        // therefore no action required.
        if (null === $routine->getRoutineId()) {
            return true;
        }

        // abort if the given routine was not found in
        // the database.
        $ar_routine = ilSrRoutine::find($routine->getRoutineId());
        if (null === $ar_routine) {
            return false;
        }

        // iterate through every relation type.
        // this needs to be done, as ilDB does not yet
        // support constraints.
        foreach ($this->getRelations($routine) as $relations_of_type) {
            // if there are relations of the current type,
            // delete them all.
            foreach ($relations_of_type as $relation) {
                $relation->delete();
            }
        }

        // finally, delete the routine itself and return.
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
     * @return array<string, ActiveRecord[]>
     */
    protected function getRelations(IRoutine $routine) : array
    {
        $relations = [];

        $relations[IRule::class] = ilSrRoutineRule::where([
            IRoutineRuleRelation::F_ROUTINE_ID => $routine->getRoutineId(),
        ])->get();

        $relations[INotification::class] = ilSrRoutineNotification::where([
            IRoutineNotificationRelation::F_ROUTINE_ID => $routine->getRoutineId(),
        ])->get();

        $relations[IRoutineWhitelist::class] = ilSrRoutineWhitelist::where([
            IRoutineWhitelist::F_ROUTINE_ID => $routine->getRoutineId(),
        ])->get();

        return $relations;
    }

    /**
     * Returns all parent ids for given ref-id recursively.
     *
     * @param int $ref_id
     * @return array|null
     */
    public function getParentIdsRecursively(int $ref_id) : ?array
    {
        static $parents;

        $parent_id = $this->tree->getParentId($ref_id);
        // type-cast is not redundant, as getParentId() returns
        // a string (not as stated by the phpdoc).
        if (null !== $parent_id && 0 < (int) $parent_id) {
            $parents[] = (int) $parent_id;
            $this->getParentIdsRecursively((int) $parent_id);
        }

        return (!empty($parents)) ? $parents : null;
    }

    /**
     * @return IRoutine[]|null
     */
    protected function getAllAsDTO() : array
    {
        /** @var $ar_routines ilSrRoutine[] */
        $ar_routines = ilSrRoutine::get();
        if (empty($ar_routines)) {
            return [];
        }

        $dto_array = [];
        foreach ($ar_routines as $ar_routine) {
            $dto_array[] = $this->transformToDTO($ar_routine);
        }

        return $dto_array;
    }

    /**
     * @return array<int, array>
     */
    protected function getAllAsArray() : array
    {
        /** @var $ar_routines ilSrRoutine[] */
        $ar_routines = ilSrRoutine::get();
        if (empty($ar_routines)) {
            return [];
        }

        $array = [];
        foreach ($ar_routines as $ar_routine) {
            $array[] = $this->transformToArray($ar_routine);
        }

        return $array;
    }

    /**
     * transforms an ActiveRecord instance into a DTO.
     *
     * @param ilSrRoutine $ar_routine
     * @return IRoutine
     */
    protected function transformToDTO(ilSrRoutine $ar_routine) : IRoutine
    {
        return new Routine(
            $ar_routine->getName(),
            $ar_routine->getRefId(),
            $ar_routine->isActive(),
            $ar_routine->getOriginType(),
            $ar_routine->getOwnerId(),
            $ar_routine->getCreationDate(),
            $ar_routine->isOptOutPossible(),
            $ar_routine->getExecutionDates(),
            $ar_routine->getElongationDays(),
            $ar_routine->getRoutineId()
        );
    }

    /**
     * transforms an ActiveRecord instance into array-data.
     * (primarily used for TableGUI's)
     *
     * @param ilSrRoutine $ar_routine
     * @return array
     */
    protected function transformToArray(ilSrRoutine $ar_routine) : array
    {
        return [
            IRoutine::F_ROUTINE_ID           => $ar_routine->getRoutineId(),
            IRoutine::F_NAME                 => $ar_routine->getName(),
            IRoutine::F_REF_ID               => $ar_routine->getRefId(),
            IRoutine::F_ACTIVE               => $ar_routine->isActive(),
            IRoutine::F_ORIGIN_TYPE          => $ar_routine->getOriginType(),
            IRoutine::F_OWNER_ID             => $ar_routine->getOwnerId(),
            IRoutine::F_CREATION_DATE        => $ar_routine->getCreationDate(),
            IRoutine::F_OPT_OUT_POSSIBLE     => $ar_routine->isOptOutPossible(),
            IRoutine::F_ELONGATION_DAYS      => $ar_routine->getElongationDays(),
            IRoutine::F_EXECUTIONS_DATES     => $ar_routine->getExecutionDates(),
        ];
    }
}