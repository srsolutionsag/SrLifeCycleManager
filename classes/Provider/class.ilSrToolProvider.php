<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Config\IConfig;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

/**
 * Class ilSrToolProvider provides ILIAS with the plugin's tools.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The provider is currently only interested in the repository context, as we want
 * to allow our tools to appear just within course objects, if configured.
 */
class ilSrToolProvider extends AbstractDynamicToolPluginProvider
{
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
        // if the user is within a possibly new or existing scope, we
        // must provide ilSrRoutineGUI with it's ref-id.
        if (null !== ($scope = $this->getCurrentScopeFromRequest())) {
            $this->dic->ctrl()->setParameterByClass(
                ilSrRoutineGUI::class,
                ilSrRoutineGUI::QUERY_PARAM_ROUTINE_SCOPE,
                $scope
            );
        }

        return [
            $this->factory
                ->tool($this->if->identifier($this->getPluginID() . '_tool'))
                ->withTitle($this->plugin->txt('tool_main_entry'))
                ->withAvailableCallable($this->getToolAvailabilityClosure())
                ->withVisibilityCallable($this->getToolVisibilityClosure())
                ->withContentWrapper(
                    function () : ILIAS\UI\Component\Component {
                        // although this method accepts any UI component, it's
                        // renderer can only manage legacy components. therefore,
                        // this component is misused to wrap a rendered component.
                        return $this->dic->ui()->factory()->legacy(
                            $this->dic->ui()->renderer()->render(
                                $this->dic->ui()->factory()->button()->standard(
                                    $this->plugin->txt('take me to routines!'),
                                    ilSrLifeCycleManagerDispatcher::buildFullyQualifiedLinkTarget(
                                        ilSrRoutineGUI::class,
                                        ilSrRoutineGUI::CMD_INDEX
                                    )
                                )
                            )
                        );
                    }
                )
            ,
        ];
    }

    /**
     * Returns a closure that determines the visibility of the tool.
     *
     * @return Closure
     */
    protected function getToolVisibilityClosure() : Closure
    {
        return function() : bool {
            // fetch configuration and check if the necessary
            // config exists and get its value.
            /** @var $config IConfig[] */
            $config              = ilSrConfig::get();
            $cnf_show_routines   = (isset($config[IConfig::CNF_SHOW_ROUTINES]) && $config[IConfig::CNF_SHOW_ROUTINES]->getValue());
            $cnf_create_routines = (isset($config[IConfig::CNF_CREATE_ROUTINES]) && $config[IConfig::CNF_CREATE_ROUTINES]->getValue());

            // save access checks in variables for readability.
            $user_id             = $this->dic->user()->getId();
            $is_user_privileged  = ilSrAccess::isUserAssignedToConfiguredRole($user_id);
            $is_user_admin       = ilSrAccess::isUserAdministrator($user_id);

            // the returned boolean values cover the following
            // scenarios, which all determine if the tool should
            // be visible or not:
            //
            //      1) the user is administrator
            //      2) it's configured that configured roles can manage
            //         routines via tool
            //      3) it's configured that active roles are displayed
            //
            // the order is important as we are checking them in one
            // statement.
            return $is_user_admin || ($cnf_create_routines && $is_user_privileged) || $cnf_show_routines;
        };
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
     * Returns the ref-id (scope) provided in the current request.
     *
     * @return int|null
     */
    protected function getCurrentScopeFromRequest() : ?int
    {
        $target = $this->dic->http()->request()->getQueryParams()['target'] ?? null;
        $ref_id = $this->dic->http()->request()->getQueryParams()['ref_id'] ?? null;

        // if a ref-id parameter is provided the value can
        // certainly be used.
        if (null !== $ref_id) return (int) $ref_id;

        // if a target parameter is provided the object was
        // linked via ilLink::_getLink() and must be checked.
        if (null !== $target) {
            // apply regex that matches anything after '_' in order
            // to dodge the type-prefix when a goto-link is provided.
            preg_match('/(?<=(_)).*/', $target, $matches);

            if (!empty($matches[0])) return (int) $matches[0];
        }

        return null;
    }
}