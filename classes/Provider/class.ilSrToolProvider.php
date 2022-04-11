<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
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
    protected const MSG_AFFECTED_ROUTINES = 'msg_affected_routines';
    protected const ACTION_ROUTINE_MANAGE = 'action_routine_manage';

    /**
     * @var int|null
     */
    protected $request_object;

    /**
     * @var ComparisonFactory
     */
    protected $comparisons;

    /**
     * @var RepositoryFactory
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
        if (null !== $this->request_object) {
            $this->keepObjectAlive($this->request_object);
        }

        $this->comparisons = new ComparisonFactory(
            new RequirementFactory($this->dic->database()),
            new AttributeFactory()
        );

        $this->repository = new RepositoryFactory(
            new ilSrGeneralRepository($this->dic->database(), $this->dic->repositoryTree(), $this->dic->rbac()),
            new ilSrConfigRepository($this->dic->database(), $this->dic->rbac()),
            new ilSrRoutineRepository($this->dic->database(), $this->dic->repositoryTree()),
            new ilSrRoutineAssignmentRepository($this->dic->database()),
            new ilSrRuleRepository($this->dic->database(), $this->dic->repositoryTree()),
            new ilSrNotificationRepository($this->dic->database()),
            new ilSrWhitelistRepository($this->dic->database())
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
            if ($this->shouldRenderRoutineList()) {
                $html .= $this->wrapHtml(
                    $this->renderActiveRoutineList($this->request_object)
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
    protected function renderActiveRoutineList(int $ref_id) : string
    {
        try {
            $object = ilObjectFactory::getInstanceByRefId($ref_id);
        } catch (Throwable $t) {
            return '';
        }

        /** @var $translator ITranslator */
        $translator = $this->plugin;

        $routine_list = new ilSrRoutineList(
            $this->dic->ui()->renderer(),
            $this->dic->ui()->factory(),
            $this->repository->routine(),
            $this->repository->rule(),
            $this->repository->whitelist(),
            $this->comparisons,
            $translator,
            $object,
            $this->dic->ctrl()
        );

        if (0 < $routine_list->getAffectedRoutineCount()) {
            ilUtil::sendQuestion($this->plugin->txt(self::MSG_AFFECTED_ROUTINES));
        }

        return $routine_list->render();
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
                $this->plugin->txt(ilSrToolbarManager::ACTION_ASSIGNMENT_ADD),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineAssignmentGUI::class,
                    ilSrRoutineAssignmentGUI::CMD_ASSIGNMENT_EDIT
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
            ilSrRoutineAssignmentGUI::PARAM_ASSIGNED_REF_ID,
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
    protected function shouldRenderRoutineList() : bool
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