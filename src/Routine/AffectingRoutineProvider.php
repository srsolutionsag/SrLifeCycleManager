<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AffectingRoutineProvider
{
    protected IRoutineRepository $routine_repository;

    protected ApplicabilityChecker $applicability_checker;

    public function __construct(IRoutineRepository $routine_repository, ApplicabilityChecker $applicability_checker)
    {
        $this->routine_repository = $routine_repository;
        $this->applicability_checker = $applicability_checker;
    }

    /**
     * Returns all routines that are assigned to the given object
     * and are applicable, whereas whitelisted routines are ignored.
     *
     * @return IRoutine[]
     */
    public function getDeletingRoutines(ilObject $object): array
    {
        return array_filter(
            $this->routine_repository->getAllForComparison(
                $object->getRefId(),
                $object->getType()
            ),
            fn(IRoutine $routine): bool => $this->applicability_checker->isApplicable($routine, $object)
        );
    }

    /**
     * Returns all routines that are assigned to the given object
     * and are applicable, regardless of any whitelist entries.
     *
     * @return IRoutine[]
     */
    public function getAffectingRoutines(ilObject $object): array
    {
        return array_filter(
            $this->routine_repository->getAllByRefId($object->getRefId()),
            fn(IRoutine $routine): bool => $this->applicability_checker->isApplicable($routine, $object)
        );
    }
}
