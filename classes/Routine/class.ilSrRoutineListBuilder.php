<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Item\Item;
use ILIAS\UI\Factory;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineListBuilder
{
    use DateTimeHelper;

    // ilSrRoutineListBuilder language variables:
    protected const ACTION_ROUTINE_ASSIGNMENT_REMOVE = 'action_list_routine_assignment_remove';
    protected const ACTION_ROUTINE_ASSIGNMENT_EDIT = 'action_list_routine_assignment_edit';
    protected const ACTION_ROUTINE_EDIT = 'action_list_routine_edit';
    protected const ACTION_ROUTINE_EXTEND = 'action_routine_extend';
    protected const ACTION_ROUTINE_OPT_OUT = 'action_routine_opt_out';
    protected const ACTION_ROUTINE_OPT_OUT_UNDO = 'action_routine_opt_out_undo';
    protected const LABEL_ROUTINE_CREATION_DATE = 'label_routine_creation_date';
    protected const LABEL_ROUTINE_OWNER_UNKNOWN = 'label_routine_owner_unknown';
    protected const LABEL_ROUTINE_OWNER = 'label_routine_owner';
    protected const LABEL_ROUTINE_DELETION_DATE = 'label_routine_deletion_date';
    protected const LABEL_WHITELIST_EXPIRY_DATE = 'label_whitelist_expiry_date';
    protected const LABEL_POSTPONED_INDEFINITELY = 'label_postponed_indefinitely';
    protected const MSG_NO_AVAILABLE_ROUTINES = 'msg_no_available_routines';

    /**
     * @var array<int, IWhitelistEntry|null>
     */
    protected static $whitelist_entries = [];

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var IRoutineAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var bool
     */
    protected $are_routines_affected = false;

    /**
     * @var IRoutine[]
     */
    protected $routines = [];

    /**
     * @var ilObject|null
     */
    protected $object;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param Factory                      $ui_factory
     * @param IRoutineAssignmentRepository $assignment_repository
     * @param IRoutineRepository           $routine_repository
     * @param IWhitelistRepository         $whitelist_repository
     * @param ITranslator                  $translator
     * @param ilSrAccessHandler            $access_handler
     * @param ilCtrl                       $ctrl
     */
    public function __construct(
        Factory $ui_factory,
        IRoutineAssignmentRepository $assignment_repository,
        IRoutineRepository $routine_repository,
        IWhitelistRepository $whitelist_repository,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilCtrl $ctrl
    ) {
        $this->ui_factory = $ui_factory;
        $this->assignment_repository = $assignment_repository;
        $this->routine_repository = $routine_repository;
        $this->whitelist_repository = $whitelist_repository;
        $this->translator = $translator;
        $this->access_handler = $access_handler;
        $this->ctrl = $ctrl;
    }

    /**
     * @param IRoutine[] $routines
     * @return self
     */
    public function withAssignedRoutines(array $routines): self
    {
        $this->routines = $routines;
        $this->are_routines_affected = false;

        return $this;
    }

    /**
     * @param IRoutine[] $routines
     * @return self
     */
    public function withAffectingRoutines(array $routines): self
    {
        $this->routines = $routines;
        $this->are_routines_affected = true;

        return $this;
    }

    /**
     * @param ilObject $object
     * @return self
     */
    public function withCurrentObject(ilObject $object): self
    {
        $this->object = $object;
        $this->setObjectParameters($object);

        return $this;
    }

    /**
     * @param string $title
     * @return self
     */
    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return self
     */
    public function reset(): self
    {
        $this->clearObjectParameters();

        $this->are_routines_affected = false;
        $this->routines = [];
        $this->object = null;
        $this->title = null;

        return $this;
    }

    /**
     * @return Group
     */
    public function getList(): Group
    {
        if (null === $this->title) {
            throw new LogicException("You must provide a valid title first (see withTitle()).");
        }

        $items = [];
        foreach ($this->routines as $routine) {
            $this->setActionParameters($routine);
            $items[] = $this->getRoutineItem($routine);
            $this->clearActionParameters();
        }

        if (empty($items)) {
            $items[] = $this->getEmptyItem();
        }

        return $this->ui_factory
            ->item()
            ->group($this->title, $items);
    }

    /**
     * @param IRoutine $routine
     * @return Item
     */
    protected function getRoutineItem(IRoutine $routine): Item
    {
        $actions = $this->getRoutineActions($routine);

        $properties = ($this->are_routines_affected) ?
            $this->getRoutineProperties($routine) : [];

        return $this->ui_factory
            ->item()
            ->standard($routine->getTitle())
            ->withActions(
                $this->ui_factory->dropdown()->standard($actions)
            )
            ->withProperties($properties);
    }

    /**
     * @return Item
     */
    protected function getEmptyItem(): Item
    {
        return $this->ui_factory->item()->standard(
            $this->translator->txt(self::MSG_NO_AVAILABLE_ROUTINES)
        );
    }

    /**
     * @param IRoutine $routine
     * @return array
     */
    protected function getRoutineProperties(IRoutine $routine): array
    {
        $whitelist_entry = $this->getRoutineWhitelistEntry($routine);

        // add initial deletion date to properties.
        $properties[$this->translator->txt(self::LABEL_ROUTINE_DELETION_DATE)] = $this->getPrettyDateString(
            $this->routine_repository->getDeletionDate($routine, $this->object->getRefId())
        );

        if (null !== $whitelist_entry) {
            if ($whitelist_entry->isOptOut()) {
                // add indefinitely as expiry if the object is opted-out.
                $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = $this->translator->txt(
                    self::LABEL_POSTPONED_INDEFINITELY
                );
            } elseif (null !== $whitelist_entry->getExpiryDate()) {
                // add the expiry-date if the whitelist entry has one.
                $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = $this->getPrettyDateString(
                    $whitelist_entry->getExpiryDate()
                );
            }
        } else {
            // add an empty string as an expiry date the object isn't whitelisted yet.
            $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = '-';
        }

        return $properties;
    }

    /**
     * @param IRoutine $routine
     * @return array
     */
    protected function getRoutineActions(IRoutine $routine): array
    {
        $actions[self::ACTION_ROUTINE_EDIT] = $this->ui_factory->button()->shy(
            $this->translator->txt(self::ACTION_ROUTINE_EDIT),
            ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::CMD_ROUTINE_EDIT
            )
        );

        if (null === $this->object) {
            return $actions;
        }

        // if the current routines are not affecting ones, or the
        // current object is the repository root, the further actions
        // wouldn't make sens because the object will or cannot be
        // deleted.
        if (!$this->are_routines_affected || 1 === $this->object->getRefId()) {
            $exact_assignment = $this->assignment_repository->get($routine->getRoutineId(), $this->object->getRefId());

            if (null !== $exact_assignment) {
                $actions[self::ACTION_ROUTINE_ASSIGNMENT_REMOVE] = $this->ui_factory->button()->shy(
                    $this->translator->txt(self::ACTION_ROUTINE_ASSIGNMENT_REMOVE),
                    ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                        ilSrRoutineAssignmentGUI::class,
                        ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_DELETE
                    )
                );

                $actions[self::ACTION_ROUTINE_ASSIGNMENT_EDIT] = $this->ui_factory->button()->shy(
                    $this->translator->txt(self::ACTION_ROUTINE_ASSIGNMENT_EDIT),
                    ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                        ilSrRoutineAssignmentGUI::class,
                        ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_EDIT
                    )
                );
            }

            return $actions;
        }

        $whitelist_entry = $this->getRoutineWhitelistEntry($routine);

        if (null !== $routine->getElongation()) {
            $actions[self::ACTION_ROUTINE_EXTEND] = $this->ui_factory->button()->shy(
                sprintf(
                    $this->translator->txt(self::ACTION_ROUTINE_EXTEND),
                    $routine->getElongation()
                ),
                ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_POSTPONE
                )
            );

            if (null !== $whitelist_entry && (
                $whitelist_entry->isOptOut() ||
                $this->isWhitelistEntryCool($routine, $whitelist_entry)
            )) {
                $actions[self::ACTION_ROUTINE_EXTEND] =
                    $actions[self::ACTION_ROUTINE_EXTEND]->withUnavailableAction();
            }
        }

        if ($this->access_handler->isAdministrator() || $routine->hasOptOut()) {
            $actions[self::ACTION_ROUTINE_OPT_OUT] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT),
                ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT
                )
            );

            $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO] = $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_ROUTINE_OPT_OUT_UNDO),
                ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT_UNDO
                )
            );

            if (null !== $whitelist_entry && $whitelist_entry->isOptOut()) {
                $actions[self::ACTION_ROUTINE_OPT_OUT] =
                    $actions[self::ACTION_ROUTINE_OPT_OUT]->withUnavailableAction();
            } else {
                $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO] =
                    $actions[self::ACTION_ROUTINE_OPT_OUT_UNDO]->withUnavailableAction();
            }
        }

        return $actions;
    }

    /**
     * Returns whether the given whitelist entry has cooled down yet or not.
     *
     * @param IRoutine        $routine
     * @param IWhitelistEntry $whitelist_entry
     * @return bool
     */
    protected function isWhitelistEntryCool(IRoutine $routine, IWhitelistEntry $whitelist_entry) : bool
    {
        return (
            null !== ($cooldown = $routine->getElongationCooldown()) &&
            null !== ($whitelist_date = $whitelist_entry->getDate()) &&
            $this->getCurrentDate() < $whitelist_date->add(
                new DateInterval("P{$cooldown}D")
            )
        );
    }

    /**
     * This method should be used to retrieve whitelist entries, because
     * it caches them mapped to the routine.
     *
     * @param IRoutine $routine
     * @return IWhitelistEntry|null
     */
    protected function getRoutineWhitelistEntry(IRoutine $routine): ?IWhitelistEntry
    {
        if (null === $this->object) {
            return null;
        }

        if (isset(self::$whitelist_entries[$routine->getRoutineId()])) {
            return self::$whitelist_entries[$routine->getRoutineId()];
        }

        self::$whitelist_entries[$routine->getRoutineId()] = $this->whitelist_repository->get(
            $routine,
            $this->object->getRefId()
        );

        return self::$whitelist_entries[$routine->getRoutineId()];
    }

    /**
     * Helper function that registers the routine id parameter for
     * the necessary GUI classes.
     *
     * @param IRoutine $routine
     */
    protected function setActionParameters(IRoutine $routine): void
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
     * Helper function that clears the routine id parameter for
     * the necessary GUI classes.
     */
    protected function clearActionParameters(): void
    {
        $this->ctrl->clearParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_ROUTINE_ID
        );

        $this->ctrl->clearParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_ROUTINE_ID
        );

        $this->ctrl->clearParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID
        );
    }

    /**
     * Helper function that registers the object ref-id parameter for
     * the necessary GUI classes.
     *
     * @param ilObject $object
     */
    protected function setObjectParameters(ilObject $object): void
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID,
            $this->object->getRefId()
        );
    }

    /**
     * Helper function that clears the object ref-id parameter for
     * the necessary GUI classes.
     */
    protected function clearObjectParameters(): void
    {
        $this->ctrl->clearParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_OBJECT_REF_ID
        );

        $this->ctrl->clearParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_OBJECT_REF_ID
        );

        $this->ctrl->clearParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID
        );
    }
}
