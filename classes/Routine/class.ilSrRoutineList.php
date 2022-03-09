<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Comparison;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Listing\Descriptive;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineList
{
    // ilSrRoutineList language variables:
    protected const ACTION_ROUTINE_EXTEND = 'action_routine_extend';
    protected const ACTION_ROUTINE_OPT_OUT = 'action_routine_opt_out';
    protected const ACTION_ROUTINE_VIEW = 'action_routine_view';

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var RequirementFactory
     */
    protected $requirement_factory;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var Descriptive
     */
    protected $list;

    /**
     * @param Renderer             $renderer
     * @param Factory              $ui_factory
     * @param IRoutineRepository   $routine_repository
     * @param IRuleRepository      $rule_repository
     * @param IWhitelistRepository $whitelist_repository
     * @param AttributeFactory     $attribute_factory
     * @param RequirementFactory   $requirement_factory
     * @param ITranslator          $translator
     * @param ilObject             $object
     * @param ilCtrl               $ctrl
     */
    public function __construct(
        Renderer $renderer,
        Factory $ui_factory,
        IRoutineRepository $routine_repository,
        IRuleRepository $rule_repository,
        IWhitelistRepository $whitelist_repository,
        AttributeFactory $attribute_factory,
        RequirementFactory $requirement_factory,
        ITranslator $translator,
        ilObject $object,
        ilCtrl $ctrl
    ) {
        $this->renderer = $renderer;
        $this->ui_factory = $ui_factory;
        $this->routine_repository = $routine_repository;
        $this->rule_repository = $rule_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->attribute_factory = $attribute_factory;
        $this->requirement_factory = $requirement_factory;
        $this->translator = $translator;
        $this->object = $object;
        $this->ctrl = $ctrl;

        $this->list = $this->getList();
    }

    /**
     * @return string
     */
    public function render() : string
    {
        // only render the list if necessary (items were added).
        if (!empty($this->list->getItems())) {
            return $this->renderer->render($this->list);
        }

        return '';
    }

    /**
     * @return Descriptive
     */
    protected function getList() : Descriptive
    {
        $list_entries = [];
        $duplicates = [];

        foreach ($this->getAffectedRoutines() as $routine) {
            // skip routines which the object was opted-out from.
            $whitelist_entry = $this->whitelist_repository->get($routine, $this->object->getRefId());
            if (null !== $whitelist_entry && $whitelist_entry->isOptOut()) {
                continue;
            }
            
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

            $list_entries[$routine_title] = $this->renderer->render(
                $this->getListEntryActions($routine)
            );
        }

        return $this->ui_factory->listing()->descriptive($list_entries);
    }

    /**
     * @return IRoutine[]
     */
    protected function getAffectedRoutines() : array
    {
        $affected_by_routines = [];
        foreach ($this->rule_repository->getByRefIdAndRoutineType(
            $this->object->getRefId(),
            $this->object->getType()
        ) as $rule) {
            $requirement = $this->requirement_factory->getRequirement($this->object);
            $comparison = new Comparison(
                $this->attribute_factory,
                $requirement,
                $rule
            );

            if ($comparison->isApplicable()) {
                // use routine-id as array key to prevent duplicate entries.
                $affected_by_routines[$rule->getRoutineId()] = $this->routine_repository->get($rule->getRoutineId());
            }
        }

        return $affected_by_routines;
    }

    /**
     * @param IRoutine $routine
     * @return Shy[]
     */
    protected function getListEntryActions(IRoutine $routine) : array
    {
        $this->setActionParameters($routine->getRoutineId());

        $actions[] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ROUTINE_VIEW),
            ilSrLifeCycleManagerDispatcher::getLinkTarget(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_INDEX
            )
        );

        if (1 <= $routine->getElongation()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_EXTEND),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_ROUTINE_EXTEND
                )
            );
        }

        if ($routine->hasOptOut()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_ROUTINE_OPT_OUT
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