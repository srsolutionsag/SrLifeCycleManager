<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Listing\Descriptive;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineListBuilder
{
    // ilSrRoutineList language variables:
    protected const ACTION_ROUTINE_EXTEND = 'action_routine_extend';
    protected const ACTION_ROUTINE_OPT_OUT = 'action_routine_opt_out';
    protected const ACTION_ROUTINE_REMOVE = 'action_routine_assignment_remove';

    /**
     * @var IRoutineAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param IRoutineAssignmentRepository $assignment_repository
     * @param ITranslator                  $translator
     * @param Factory                      $ui_factory
     * @param Renderer                     $renderer
     * @param ilObject                     $object
     * @param ilCtrl                       $ctrl
     */
    public function __construct(
        IRoutineAssignmentRepository $assignment_repository,
        ITranslator $translator,
        Factory $ui_factory,
        Renderer $renderer,
        ilObject $object,
        ilCtrl $ctrl
    ) {
        $this->assignment_repository = $assignment_repository;
        $this->translator = $translator;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->object = $object;
        $this->ctrl = $ctrl;
    }

    /**
     * @param array $routines
     * @return Descriptive
     */
    public function getList(array $routines) : Descriptive
    {
        $list_entries = $duplicates = [];

        foreach ($routines as $routine) {
            // retrieve routine title and ensure there are no duplicates,
            // because the title must be used as array-index for Descriptive.
            $routine_title = $routine->getTitle();
            if (isset($list_entries[$routine_title])) {
                if (isset($duplicates[$routine_title])) {
                    $count = $duplicates[$routine_title]++;
                } else {
                    $duplicates[$routine_title] = 1;
                    $count = 1;
                }

                $routine_title .= " ($count)";
            }

            $list_entries[$routine_title] = "
                <div style=\"margin-bottom: 20px;\">
                    {$this->renderer->render($this->getListEntryActions($routine))}                
                </div>
            ";
        }

        return $this->ui_factory->listing()->descriptive($list_entries);
    }

    /**
     * @param IRoutine $routine
     * @return Shy[]
     */
    protected function getListEntryActions(IRoutine $routine) : array
    {
        $this->setActionParameters($routine->getRoutineId());

        $actions = [];

        if (null !== $this->assignment_repository->get($routine->getRoutineId(), $this->object->getRefId())) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_REMOVE),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_DELETE
                )
            );

            return $actions;
        }

        if (1 <= $routine->getElongation()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_EXTEND),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_POSTPONE
                )
            );
        }

        if ($routine->hasOptOut()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT
                )
            );
        }

        return $actions;
    }

    /**
     * @param int $routine_id
     * @return void
     */
    protected function setActionParameters(int $routine_id) : void
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID,
            $routine_id
        );
    }
}