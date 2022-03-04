<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\IRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Component;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Comparison;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrToolProvider provides ILIAS with the plugin's tools.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The provider is currently only interested in the repository context, as we want
 * to allow our tools to appear just within course objects, if configured.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrToolProvider extends AbstractDynamicToolPluginProvider
{
    // ilSrToolProvider language variables:
    protected const MSG_PRIVILEGED_USER = 'msg_privileged_user';
    protected const MSG_AFFECTED_ROUTINES = 'msg_affected_routines';
    protected const ACTION_ROUTINE_MANAGE = 'action_routine_manage';
    protected const ACTION_ROUTINE_EXTEND = 'action_routine_extend';
    protected const ACTION_ROUTINE_OPT_OUT = 'action_routine_opt_out';
    protected const ACTION_ROUTINE_VIEW = 'action_routine_view';

    /**
     * @var int|null
     */
    protected $request_object;

    /**
     * @var RequirementFactory
     */
    protected $requirements;

    /**
     * @var AttributeFactory
     */
    protected $attributes;

    /**
     * @var IRepository
     */
    protected $repository;

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

        // keep the ref-id or target of the current request alive, it
        // will be treated as routine-ref-id to retrieve routines.
        if (null !== $this->request_object) {
            $this->dic->ctrl()->setParameterByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::PARAM_OBJECT_REF_ID,
                $this->request_object
            );
        }

        $this->requirements = new RequirementFactory($this->dic->database());
        $this->attributes = new AttributeFactory();
        $this->repository = new ilSrLifeCycleManagerRepository(
            $this->dic->database(),
            $this->dic->rbac(),
            $this->dic->repositoryTree()
        );

        $this->config = $this->repository->config()->get();
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
            // abort if the requested object is not available.
            if (null === $this->request_object) {
                return $this->dic->ui()->factory()->legacy($html);
            }

            if ($this->shouldRenderRoutineList()) {
                $html .= $this->wrapHtml(
                    $this->renderActiveRoutineList($this->request_object)
                );
            }

            if ($this->shouldRenderRoutineControls()) {
                $html .= $this->wrapHtml(
                    $this->renderPrivilegedRoutineControls()
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
    protected function renderActiveRoutineList(int $ref_id) : string
    {
        $object_type = ilObject2::_lookupType($ref_id, true);
        try {
            $object = ilObjectFactory::getInstanceByRefId($ref_id);
        } catch (Throwable $t) {
            return '';
        }

        $rules = $this->repository->rule()->getByRoutineRefIdAndType($ref_id, $object_type);

        $affected_by = [];
        foreach ($rules as $rule) {
            $requirement = $this->requirements->getRequirement($object);
            $comparison = new Comparison(
                $this->attributes,
                $requirement,
                $rule
            );

            if ($comparison->isApplicable()) {
                // map routine-id to itself to avoid duplicates.
                $affected_by[$rule->getRoutineId()] = $rule->getRoutineId();
            }
        }

        $duplicate_counts = [];
        $list_entries = [];
        if (!empty($affected_by)) {
            foreach ($affected_by as $routine_id) {
                $routine = $this->repository->routine()->get($routine_id);
                if (null === $routine ||
                    $this->repository->routine()->whitelist()->isObjectOptedOut(
                        $routine,
                        $ref_id
                    )
                ) {
                    continue;
                }

                $routine_title = $routine->getTitle();
                if (isset($routine_entries[$routine_title])) {
                    if (isset($duplicate_counts[$routine_title])) {
                        $duplicate_counts[$routine_title]++;
                    } else {
                        $duplicate_counts[$routine_title] = 2;
                    }

                    $routine_title .= " ($duplicate_counts[$routine_title])";
                }

                $this->dic->ctrl()->setParameterByClass(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::PARAM_ROUTINE_ID,
                    $routine->getRoutineId()
                );

                $actions = [];

                $actions[] = $this->dic->ui()->factory()->button()->shy(
                    $this->plugin->txt(self::ACTION_ROUTINE_VIEW),
                    ilSrLifeCycleManagerDispatcher::getLinkTarget(
                        ilSrRoutineGUI::class,
                        ilSrRoutineGUI::CMD_INDEX
                    )
                );

                if (1 < $routine->getElongation()) {
                    $actions[] = $this->dic->ui()->factory()->button()->shy(
                        $this->plugin->txt(self::ACTION_ROUTINE_EXTEND),
                        ilSrLifeCycleManagerDispatcher::getLinkTarget(
                            ilSrRoutineGUI::class,
                            ilSrRoutineGUI::CMD_ROUTINE_EXTEND
                        )
                    );
                }

                if ($routine->hasOptOut()) {
                    $actions[] = $this->dic->ui()->factory()->button()->shy(
                        $this->plugin->txt(self::ACTION_ROUTINE_OPT_OUT),
                        ilSrLifeCycleManagerDispatcher::getLinkTarget(
                            ilSrRoutineGUI::class,
                            ilSrRoutineGUI::CMD_ROUTINE_OPT_OUT
                        )
                    );
                }

                $list_entries[$routine_title] = $this->dic->ui()->renderer()->render($actions);
            }
        }

        if (empty($list_entries)) {
            return '';
        }

        return $this->dic->ui()->renderer()->render([
            $this->dic->ui()->factory()->messageBox()->confirmation(
                $this->plugin->txt(self::MSG_AFFECTED_ROUTINES)
            ),
            $this->dic->ui()->factory()->listing()->descriptive($list_entries)
        ]);
    }

    /**
     * Returns the HTML for privileged users, that displays an info message
     * and some routine-action buttons.
     *
     * @return string
     */
    protected function renderPrivilegedRoutineControls() : string
    {
        return $this->dic->ui()->renderer()->render([
            // info-message that current user can manage routines.
            // $this->dic->ui()->factory()->messageBox()->info(
            //     $this->plugin->txt(self::MSG_PRIVILEGED_USER)
            // ),

            // action-button to add new routines at current position.
            $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(ilSrToolbarManager::ACTION_ROUTINE_ADD),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_EDIT
                )
            ),
            // action-button to see routines at current position.
            $this->dic->ui()->factory()->button()->standard(
                $this->plugin->txt(self::ACTION_ROUTINE_MANAGE),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_INDEX
                )
            ),
        ]);
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
                    $this->shouldRenderRoutineList()
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
            $this->config->createRoutinesInRepository() &&
            $this->access_handler->canManageRoutines()
        );
    }

    /**
     * Checks if the tool's affected routine-list should be rendered.
     *
     * @return bool
     */
    protected function shouldRenderRoutineList() : bool
    {
        return (
            $this->config->showRoutinesInRepository() &&
            $this->access_handler->canViewRoutines($this->request_object)
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