<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine\Provider;

use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use Generator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DeletableObjectProvider
{
    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @var IGeneralRepository
     */
    protected $general_repository;

    /**
     * @param RoutineProvider    $routine_provider
     * @param IGeneralRepository $general_repository
     */
    public function __construct(RoutineProvider $routine_provider, IGeneralRepository $general_repository)
    {
        $this->routine_provider = $routine_provider;
        $this->general_repository = $general_repository;
    }

    /**
     * Yields all deletable repository objects.
     *
     * @return DeletableObject[]|Generator
     */
    public function getDeletableObjects() : Generator
    {
        foreach ($this->general_repository->getRepositoryObjects() as $object) {
            $routines = $this->routine_provider->getAffectingRoutines($object);
            if (!empty($routines)) {
                yield new DeletableObject(
                    $object,
                    $routines
                );
            }
        }
    }
}
