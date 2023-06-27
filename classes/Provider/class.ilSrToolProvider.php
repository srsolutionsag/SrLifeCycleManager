<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\AffectingRoutineProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Component;

/**
 * Class ilSrToolProvider provides ILIAS with the plugin's tools.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
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
    protected const LABEL_AFFECTING_ROUTINES = 'label_affecting_routines';
    protected const LABEL_ASSIGNED_ROUTINES = 'label_assigned_routines';
    protected const CNF_TOOL_CONTROLS = 'cnf_tool_controls';

    /**
     * @var ilObject|null
     */
    protected $request_object;

    /**
     * @var ilSrAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var AffectingRoutineProvider
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
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts): array
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
    protected function initDependencies(): void
    {
        /** @var $component_factory ilComponentFactory */
        $component_factory = $this->dic['component.factory'];
        /** @var $plugin ilSrLifeCycleManagerPlugin */
        $plugin = $component_factory->getPlugin(ilSrLifeCycleManagerPlugin::PLUGIN_ID);

        $container = $plugin->getContainer();

        $this->request_object = $container
            ->getRepositoryFactory()
            ->general()
            ->getObject($this->getRequestedReferenceId());

        if (null !== $this->request_object) {
            $this->keepObjectAlive($this->request_object->getRefId());
        }

        $this->config = $container->getRepositoryFactory()->config()->get();
        $this->assignment_repository = $container->getRepositoryFactory()->assignment();
        $this->whitelist_repository = $container->getRepositoryFactory()->whitelist();
        $this->routine_repository = $container->getRepositoryFactory()->routine();
        $this->routine_provider = $container->getAffectingRoutineProvider();
        $this->access_handler = $container->getAccessHandler();
    }

    /**
     * Returns the tool content wrapper closure.
     *
     * @return Closure
     */
    protected function getContentWrapper(): Closure
    {
        return function (): Component {
            $html = '';
            if ($this->shouldRenderRoutineLists()) {
                $html .= $this->renderRoutineLists($this->request_object->getRefId());
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
     */
    protected function renderRoutineLists(int $ref_id): string
    {
        $object = ilObjectFactory::getInstanceByRefId($ref_id, false);

        if (null === $object) {
            return '';
        }

        /** @var $translator ITranslator */
        $translator = $this->plugin;

        $affecting_routines = $this->routine_provider->getAffectingRoutines($object);
        $assigned_routines = $this->routine_repository->getAllByRefId($object->getRefId());

        // array_udiff() didn't seem to work in certain scenarios if the
        // array sizes of both arrays were different, therefore I've used
        // this stupid loop now. this is definitely a performance killer,
        // maybe someone smart can figure this out :(.
        $assigned_but_unaffecting_routines = [];
        foreach ($assigned_routines as $assigned_routine) {
            $assigned_routine_id = $assigned_routine->getRoutineId();
            foreach ($affecting_routines as $affecting_routine) {
                if ($assigned_routine_id === $affecting_routine->getRoutineId()) {
                    continue 2;
                }
            }

            $assigned_but_unaffecting_routines[] = $assigned_routine;
        }

        $list_builder = new ilSrRoutineListBuilder(
            $this->dic->ui()->factory(),
            $this->assignment_repository,
            $this->routine_repository,
            $this->whitelist_repository,
            $translator,
            $this->access_handler,
            $this->dic->ctrl()
        );

        $affecting_routine_list = $list_builder
            ->reset()
            ->withTitle($translator->txt(self::LABEL_AFFECTING_ROUTINES))
            ->withAffectingRoutines($affecting_routines)
            ->withCurrentObject($object)
            ->getList();

        $assigned_routine_list = $list_builder
            ->reset()
            ->withTitle($translator->txt(self::LABEL_ASSIGNED_ROUTINES))
            ->withAssignedRoutines($assigned_but_unaffecting_routines)
            ->withCurrentObject($object)
            ->getList();

        return "
            <div id=\"srlcm-item-group\">
                <style>
                    /**
                     * makes columns of item properties within this container
                     * to appear as row(ish). this needs to be done because
                     * text is displayed too small within the tool context.
                     */
                    #srlcm-item-group .col-md-6 {
                        padding-bottom: 2px;
                        width: 100%;
                    }
                    
                    /**
                     * this fixes a presentation bug where long titles lead to
                     * a wrong break if an action dropdown was displayed aside.
                     * (26px is the default width of the action-dropdown)
                     */
                    #srlcm-item-group .il-item-title {
                        max-width: calc(100% - 26px);
                    }
                </style>
                {$this->dic->ui()->renderer()->render([
                    $affecting_routine_list,
                    $assigned_routine_list
                ])}
            </div>
        ";
    }

    /**
     * Returns the HTML for privileged users, that displays an info message
     * and some routine-action buttons.g
     */
    protected function renderRoutineControls(): string
    {
        $controls = [];
        if ($this->access_handler->canManageAssignments()) {
            // action-button to add new routines at current position.
            $controls[] = $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(self::ACTION_ASSIGNMENTS_MANAGE),
                ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_INDEX
                )
            );
        }

        // action-button to see routines at current position.
        if ($this->access_handler->canManageRoutines()) {
            $controls[] = $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(self::ACTION_ROUTINE_MANAGE),
                ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
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
     */
    protected function getRequestedReferenceId(): ?int
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
     */
    protected function keepObjectAlive(int $ref_id): void
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
     */
    protected function getToolAvailabilityClosure(): Closure
    {
        return function (): bool {
            // the availability of the tool depends on the
            // active state of the plugin.
            return (bool) $this->plugin->isActive();
        };
    }

    /**
     * Returns a closure that determines the visibility of the tool.
     */
    protected function getToolVisibilityClosure(): Closure
    {
        // the tool should be visible if an object was requested,
        // and at least one of the tool components should be rendered.
        return function (): bool {
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
     */
    protected function shouldRenderRoutineControls(): bool
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
     */
    protected function shouldRenderRoutineLists(): bool
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
