<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Component;

/**
 * Class ilSrToolProvider provides ILIAS with the plugin's tools.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The provider is currently only interested in the repository context, as we want
 * to allow our tools to appear just within repository objects, if configured.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrToolProvider extends AbstractDynamicToolPluginProvider
{
    // ilSrToolProvider language variables:
    protected const ACTION_ASSIGNMENTS_MANAGE = 'action_routine_assignment_manage';
    protected const ACTION_ROUTINE_MANAGE = 'action_routine_manage';
    protected const ROUTINES_HEADER = 'cnf_tool_routines_header';
    protected const ROUTINES_TRIGGERED_HEADER = 'cnf_tool_routines_triggered_header';
    protected const CNF_TOOL_CONTROLS = 'cnf_tool_controls';

    /**
     * @var int|null
     */
    protected $request_object;

    /**
     * @var ilSrAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->repository();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $this->initDependencies();

        return [
            $this->factory
                ->tool($this->if->identifier('tool'))
                ->withTitle($this->plugin->txt('tool_main_entry'))
                ->withAvailableCallable($this->getToolAvailabilityClosure())
                ->withVisibilityCallable($this->getToolVisibilityClosure())
                ->withContentWrapper($this->getContentWrapper())
            ,
        ];
    }

    /**
     * Helper function that initializes dependencies on request, because
     * the class cannot override the constructor.
     */
    protected function initDependencies() : void
    {
        $this->request_object = $this->getRequestedObject();
        if (null !== $this->request_object) {
            $this->keepObjectAlive($this->request_object);
        }

        $this->config = (new ilSrConfigRepository(
            $this->dic->database(),
            $this->dic->rbac())
        )->get();

        $this->assignment_repository = new ilSrAssignmentRepository(
            $this->dic->database(),
            $this->dic->repositoryTree()
        );

        $this->routine_repository = new ilSrRoutineRepository(
            $this->dic->database(),
            $this->dic->repositoryTree()
        );

        $this->routine_provider = new RoutineProvider(
            new ComparisonFactory(
                new RequirementFactory($this->dic->database()),
                new AttributeFactory()
            ),
            $this->routine_repository,
            new ilSrRuleRepository(
                $this->dic->database(),
                $this->dic->repositoryTree()
            )
        );

        $this->access_handler = new ilSrAccessHandler(
            $this->dic->rbac(),
            $this->config,
            $this->dic->user()
        );
    }

    /**
     * Returns the tool content wrapper closure.
     *
     * @return Closure
     */
    protected function getContentWrapper() : Closure
    {
        return function () : Component {
            $html = '';
            if ($this->shouldRenderRoutineLists()) {
                $html .= $this->renderRoutineLists($this->request_object);
            }

            if ($this->shouldRenderRoutineControls()) {
                $html .= $this->renderRoutineControls();
            }

            return $this->dic->ui()->factory()->legacy($html);
        };
    }

    /**
     * Returns the html for object administrators, that shows a list of routines
     * that currently apply to the requested object.
     *
     * @param int $ref_id
     * @return string
     */
    protected function renderRoutineLists(int $ref_id) : string
    {
        try {
            $object = ilObjectFactory::getInstanceByRefId($ref_id);
        } catch (Throwable $t) {
            return '';
        }

        /** @var $translator ITranslator */
        $translator = $this->plugin;

        $panels = [];

        $list_builder = new ilSrRoutineListBuilder(
            $this->assignment_repository,
            $this->access_handler,
            $translator,
            $this->dic->ui()->factory(),
            $this->dic->ui()->renderer(),
            $object,
            $this->dic->ctrl()
        );

        $affecting_routines = $this->routine_provider->getAffectingRoutines($object);

        if (!empty($affecting_routines)) {
            $panels[] = $this->dic->ui()->factory()->panel()->secondary()->legacy(
                $translator->txt(self::ROUTINES_TRIGGERED_HEADER),
                $this->dic->ui()->factory()->legacy(
                    $this->dic->ui()->renderer()->render(
                        $list_builder->getList($affecting_routines)
                    )
                )
            );
        }

        $routines_for_location = $this->routine_provider->getRoutinesForLocation($object);

        $diff_routines = array_udiff(
            $routines_for_location,
            $affecting_routines,
            static function (IRoutine $one, IRoutine $two) : bool {
                return $one->getRoutineId() !== $two->getRoutineId();
            }
        );

        if (!empty($diff_routines)) {
            $panels[] = $this->dic->ui()->factory()->panel()->secondary()->legacy(
                $translator->txt(self::ROUTINES_HEADER),
                $this->dic->ui()->factory()->legacy(
                    $this->dic->ui()->renderer()->render(
                        $list_builder->getList($diff_routines)
                    )
                )
            );
        }

        return $this->dic->ui()->renderer()->render(
            $panels
        );
    }

    /**
     * Returns the HTML for privileged users, that displays an info message
     * and some routine-action buttons.
     *
     * @return string
     */
    protected function renderRoutineControls() : string
    {
        $controls = [];
        if ($this->access_handler->canManageAssignments()) {
            // action-button to add new routines at current position.
            $controls[] = $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(self::ACTION_ASSIGNMENTS_MANAGE),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_INDEX
                )
            );
        }

        // action-button to see routines at current position.
        if ($this->access_handler->canManageRoutines()) {
            $controls[] = $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(self::ACTION_ROUTINE_MANAGE),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_INDEX
                )
            );
        }

        return $this->dic->ui()->renderer()->render(
            $this->dic->ui()->factory()->panel()->secondary()->legacy(
                $this->plugin->txt(self::CNF_TOOL_CONTROLS),
                $this->dic->ui()->factory()->legacy($this->dic->ui()->renderer()->render($controls))
            )
        );
    }

    /**
     * Returns the ref-id or target provided in the current request.
     *
     * @return int|null
     */
    protected function getRequestedObject() : ?int
    {
        $target = $this->dic->http()->request()->getQueryParams()['target'] ?? null;
        $ref_id = $this->dic->http()->request()->getQueryParams()['ref_id'] ?? null;

        // if a ref-id parameter is provided the value can be safely used.
        if (null !== $ref_id) {
            return (int) $ref_id;
        }

        // if a target parameter is provided the object was
        // linked via ilLink::_getLink() and must be checked.
        if (null !== $target) {
            // apply regex that matches anything after '_' in order
            // to dodge the type-prefix when a goto-link is provided.
            preg_match('/(?<=(_)).*/', $target, $matches);

            if (!empty($matches[0])) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    /**
     * Keeps the given object (ref-id) alive for potential redirects to
     * GUI classes of this plugin.
     *
     * @param int $ref_id
     * @return void
     */
    protected function keepObjectAlive(int $ref_id) : void
    {
        $this->dic->ctrl()->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_OBJECT_REF_ID,
            $ref_id
        );

        $this->dic->ctrl()->setParameterByClass(
            ilSrRoutineAssignmentGUI::class,
            ilSrRoutineAssignmentGUI::PARAM_OBJECT_REF_ID,
            $ref_id
        );

        $this->dic->ctrl()->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_OBJECT_REF_ID,
            $ref_id
        );

        $this->dic->ctrl()->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID,
            $ref_id
        );
    }

    /**
     * Returns a closure that determines the availability of the tool.
     *
     * @return Closure
     */
    protected function getToolAvailabilityClosure() : Closure
    {
        return function() : bool {
            // the availability of the tool depends on the
            // active state of the plugin.
            return (bool) $this->plugin->isActive();
        };
    }

    /**
     * Returns a closure that determines the visibility of the tool.
     *
     * @return Closure
     */
    protected function getToolVisibilityClosure() : Closure
    {
        // the tool should be visible if an object was requested,
        // and at least one of the tool components should be rendered.
        return function() : bool {
            return (
                null !== $this->request_object && (
                    $this->shouldRenderRoutineControls() ||
                    $this->shouldRenderRoutineLists()
                )
            );
        };
    }

    /**
     * Checks if the tool's routine-creation part should be rendered.
     *
     * @return bool
     */
    protected function shouldRenderRoutineControls() : bool
    {
        return (
            $this->config->shouldToolShowControls() &&
            (
                $this->access_handler->canManageRoutines() ||
                $this->access_handler->canManageAssignments()
            )
        );
    }

    /**
     * Checks if the tool's affected routine-list should be rendered.
     *
     * @return bool
     */
    protected function shouldRenderRoutineLists() : bool
    {
        return (
            null !== $this->request_object &&
            $this->config->shouldToolShowRoutines() &&
            (
                $this->access_handler->canManageRoutines() ||
                $this->access_handler->isAdministratorOf($this->request_object)
            )
        );
    }
}
