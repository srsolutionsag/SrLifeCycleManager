<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractRoutineList
{
    use DateTimeHelper;

    // ilSrAbstractRoutineList language variables:
    protected const ACTION_ROUTINE_ASSIGNMENT_REMOVE = 'action_list_routine_assignment_remove';
    protected const ACTION_ROUTINE_EDIT = 'action_list_routine_edit';
    protected const ACTION_ROUTINE_EXTEND = 'action_routine_extend';
    protected const ACTION_ROUTINE_OPT_OUT = 'action_routine_opt_out';
    protected const ACTION_ROUTINE_OPT_OUT_UNDO = 'action_routine_opt_out_undo';
    protected const LABEL_ROUTINE_CREATION_DATE = 'label_routine_creation_date';
    protected const LABEL_ROUTINE_OWNER_UNKNOWN = 'label_routine_owner_unknown';
    protected const LABEL_ROUTINE_OWNER = 'label_routine_owner';
    protected const MSG_NO_AVAILABLE_ROUTINES = 'msg_no_available_routines';

    /**
     * @var IRoutineAssignmentRepository
     */
    protected $assignments;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param IRoutineAssignmentRepository $assignment_repository
     * @param IWhitelistRepository         $whitelist_repository
     * @param ITranslator                  $translator
     * @param ilSrAccessHandler            $access_handler
     * @param ilObject                     $current_object
     * @param Factory                      $ui_factory
     * @param Renderer                     $renderer
     * @param ilCtrl                       $ctrl
     */
    public function __construct(
        IRoutineAssignmentRepository $assignment_repository,
        IWhitelistRepository $whitelist_repository,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilObject $current_object,
        Factory $ui_factory,
        Renderer $renderer,
        ilCtrl $ctrl
    ) {
        $this->assignments = $assignment_repository;
        $this->whitelist = $whitelist_repository;
        $this->translator = $translator;
        $this->access_handler = $access_handler;
        $this->object = $current_object;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->ctrl = $ctrl;

        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        $items = [];
        foreach ($this->getRoutines() as $routine) {
            // skip duplicate entries.
            if (null !== $items[$routine->getRoutineId()]) {
                continue;
            }

            // retrieve necessary data only once.
            $assignment = $this->assignments->get($routine->getRoutineId(), $this->object->getRefId());
            $whitelist_entry = $this->whitelist->get($routine, $this->object->getRefId());

            $items[$routine->getRoutineId()] = $this->ui_factory
                ->item()
                ->standard($routine->getTitle())
                ->withActions(
                    $this->ui_factory->dropdown()->standard(
                        $this->getRoutineActions(
                            $routine,
                            $assignment,
                            $whitelist_entry
                        )
                    )
                )
                ->withProperties(
                    $this->getRoutineProperties(
                        $routine,
                        $whitelist_entry
                    )
                );
        }

        if (empty($items)) {
            $items[] = $this->ui_factory->item()->standard(
                $this->translator->txt(self::MSG_NO_AVAILABLE_ROUTINES)
            );
        }

        return $this->renderer->render(
            $this->ui_factory->item()->group($this->getTitle(), $items)
        );
    }

    /**
     * Returns an array of buttons that contain available actions for the given routine.
     *
     * The assignment- and whitelist-entry are passed along in order to reduce the
     * amount of database-queries, all necessary data will and should be retrieved in
     * @see ilSrAbstractRoutineList::getHtml().
     *
     * @param IRoutine                $routine
     * @param IRoutineAssignment|null $assignment
     * @param IWhitelistEntry|null    $whitelist_entry
     * @return Shy[]
     */
    protected function getRoutineActions(
        IRoutine $routine,
        IRoutineAssignment $assignment = null,
        IWhitelistEntry $whitelist_entry = null
    ): array {
        $this->setRoutineActionParameters($routine);

        if (null !== $assignment) {
            $actions[self::ACTION_ROUTINE_ASSIGNMENT_REMOVE] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_ASSIGNMENT_REMOVE),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_DELETE
                )
            );
        }

        $actions[self::ACTION_ROUTINE_EDIT] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ROUTINE_EDIT),
            ilSrLifeCycleManagerDispatcher::getLinkTarget(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_ROUTINE_EDIT
            )
        );

        // if the current object is the ILIAS repository root, don't show
        // postpone- and opt-out-action (since the object cannot be deleted).
        if (1 === $this->object->getRefId()) {
            return $actions;
        }

        if (1 < $routine->getElongation()) {
            $actions[self::ACTION_ROUTINE_EXTEND] = $this->ui_factory->button()->shy(
                sprintf(
                    $this->translator->txt(self::ACTION_ROUTINE_EXTEND),
                    (string) $routine->getElongation()
                ),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_POSTPONE
                )
            );

            // deactivate the action if the whitelist-entry is not cooled down yet.
            if (null !== $whitelist_entry && null !== ($cooldown = $routine->getElongationCooldown()) &&
                $this->getCurrentDate() < $whitelist_entry->getDate()->add(new DateInterval("P{$cooldown}D"))
            ) {
                $actions[self::ACTION_ROUTINE_EXTEND] = $actions[self::ACTION_ROUTINE_EXTEND]->withUnavailableAction();
            }
        }

        // opt-outs are always available for administrators (since v1.5.0).
        if ($routine->hasOptOut() || $this->access_handler->isAdministrator()) {
            $actions[self::ACTION_ROUTINE_OPT_OUT] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT
                )
            );

            $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT_UNDO),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT_UNDO
                )
            );

            // deactivate the action if the whitelist-entry is already an opt-out,
            // otherwise deactivate the undo-action.
            if (null !== $whitelist_entry && $whitelist_entry->isOptOut()) {
                $actions[self::ACTION_ROUTINE_OPT_OUT] = $actions[self::ACTION_ROUTINE_OPT_OUT]->withUnavailableAction(
                );
            } else {
                $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO] = $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO]->withUnavailableAction(
                );
            }
        }

        return $actions;
    }

    /**
     * Registers all necessary query-parameters for the given routine.
     *
     * @param IRoutine $routine
     */
    protected function setRoutineActionParameters(IRoutine $routine): void
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_ROUTINE_ID,
            $routine->getRoutineId()
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID,
            $routine->getRoutineId()
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID,
            $routine->getRoutineId()
        );
    }

    /**
     * Returns a list of translated property-names mapped to presentable data.
     *
     * The whitelist-entry is passed along in order to reduce the amount of
     * database-queries, all necessary data will and should be retrieved in
     * @see ilSrAbstractRoutineList::getHtml().
     *
     * @param IRoutine             $routine
     * @param IWhitelistEntry|null $whitelist_entry
     * @return array<string, string>
     */
    protected function getRoutineProperties(IRoutine $routine, IWhitelistEntry $whitelist_entry = null): array
    {
        return [
            // uncomment this to display the routine creation-date.
            // $this->translator->txt(self::LABEL_ROUTINE_CREATION_DATE) => $this->getPrettyDateString(
            //     $routine->getCreationDate()
            // ),
            $this->translator->txt(self::LABEL_ROUTINE_OWNER) => (ilObjUser::_exists($routine->getOwnerId())) ?
                (new ilObjUser($routine->getOwnerId()))->getPublicName() :
                $this->translator->txt(self::LABEL_ROUTINE_OWNER_UNKNOWN)
            ,
        ];
    }

    /**
     * This method should return all routines that should be rendered in a list
     * by this class.
     *
     * @return IRoutine[]
     */
    abstract protected function getRoutines(): array;

    /**
     * This method should return a representable title that will be displayed
     * above the generated list.
     *
     * @return string
     */
    abstract protected function getTitle(): string;
}
