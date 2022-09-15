<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrAssignedRoutineList extends ilSrAbstractRoutineList
{
    // ilSrRoutineList language variables:
    protected const LABEL_ASSIGNED_ROUTINES = 'label_assigned_routines';

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @param IRoutineRepository $routine_repository
     *
     * @inheritDoc
     */
    public function __construct(
        IRoutineAssignmentRepository $assignment_repository,
        IWhitelistRepository $whitelist_repository,
        IRoutineRepository $routine_repository,
        RoutineProvider $routine_provider,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilObject $current_object,
        Factory $ui_factory,
        Renderer $renderer,
        ilCtrl $ctrl
    ) {
        parent::__construct($assignment_repository, $whitelist_repository, $translator, $access_handler, $current_object, $ui_factory, $renderer, $ctrl);

        $this->routine_repository = $routine_repository;
        $this->routine_provider = $routine_provider;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutines(): array
    {
        $assigned_routines = $this->routine_repository->getAllByRefId($this->object->getRefId());
        $affecting_routines = $this->routine_provider->getAffectingRoutines($this->object);

        return array_udiff(
            $assigned_routines,
            $affecting_routines,
            static function (IRoutine $one, IRoutine $two) : bool {
                return $one->getRoutineId() !== $two->getRoutineId();
            }
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTitle(): string
    {
        return $this->translator->txt(self::LABEL_ASSIGNED_ROUTINES);
    }
}
