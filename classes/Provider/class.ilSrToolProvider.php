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
    protected const MSG_AFFECTED_ROUTINES = 'msg_affected_routines';
    protected const MSG_ASSIGNED_ROUTINES = 'msg_assigned_routines';

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
                ->tool($this->if->identifier($this->getPluginID() . '_tool'))
                ->withTitle($this->plugin->txt('tool_main_entry'))
                ->withAvailableCallable($this->getToolAvailabilityClosure())
                ->withVisibilityCallable($this->getToolVisibilityClosure())
                ->withContentWrapper(
                    $this->getContentWrapper()
                )
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
                $html .= $this->wrapHtml(
                    $this->renderRoutineLists($this->request_object)
                );
            }

            if ($this->shouldRenderRoutineControls()) {
                $html .= $this->wrapHtml(
                    $this->renderRoutineControls()
                );
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
        $html = '';

        $list_builder = new ilSrRoutineListBuilder(
            $this->assignment_repository,
            $translator,
            $this->dic->ui()->factory(),
            $this->dic->ui()->renderer(),
            $object,
            $this->dic->ctrl()
        );

        $affected_routines = $this->routine_provider->getAffectingRoutines($object);
        if (!empty($affected_routines)) {
            $html .= $this->dic->ui()->renderer()->render(
                [
                    $this->dic->ui()->factory()->messageBox()->confirmation(
                        $translator->txt(self::MSG_AFFECTED_ROUTINES)
                    ),

                    $list_builder->getList($affected_routines),
                ]
            );
        }

        $assigned_routines = array_udiff(
            $affected_routines,
            $this->routine_repository->getAllByRefId($object->getRefId()),
            static function (IRoutine $routine_a, IRoutine $routine_b) {
                return ($routine_a->getRoutineId() - $routine_b->getRoutineId());
            }
        );

        if (!empty($assigned_routines)) {
            $html .= $this->dic->ui()->renderer()->render(
                [
                    $this->dic->ui()->factory()->messageBox()->info(
                        $translator->txt(self::MSG_ASSIGNED_ROUTINES)
                    ),

                    $list_builder->getList($assigned_routines),
                ]
            );
        }

        return $html;
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

        return $this->dic->ui()->renderer()->render($controls);
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

    /**
     * Helper function that wraps the given HTML in a div that allows
     * some space to the edge of the tool.
     *
     * @param string $html
     * @return string
     */
    protected function wrapHtml(string $html) : string
    {
        return "<div style=\"margin: 10px 10px 20px 10px;\">$html</div>";
    }
}