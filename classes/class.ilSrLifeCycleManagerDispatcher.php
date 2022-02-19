<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrLifeCycleManagerDispatcher is responsible for ALL plugins requests.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilSrLifeCycleManagerDispatcher : ilSrLifeCycleManagerConfigGUI
 *
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrConfigGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrNotificationGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRoutineGUI
 * @ilCtrl_Calls ilSrLifeCycleManagerDispatcher : ilSrRuleGUI
 */
class ilSrLifeCycleManagerDispatcher
{
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $global_template;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * ilSrLifeCycleManagerDispatcher constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->global_template = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Dispatches all plugin requests to the executing command class.
     *
     * Whenever a new command class is added the PHPDoc comments above
     * must be complemented by an according '@ilCtrl_Calls' statement.
     *
     * @throws ilCtrlException if the command class could not be loaded
     * @throws LogicException if the command class could not be found
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilSrConfigGUI::class):
                $this->ctrl->forwardCommand(new ilSrConfigGUI());
                break;
            case strtolower(ilSrNotificationGUI::class):
                $this->ctrl->forwardCommand(new ilSrNotificationGUI());
                break;
            case strtolower(ilSrRoutineGUI::class):
                $this->ctrl->forwardCommand(new ilSrRoutineGUI());
                break;
            case strtolower(ilSrRuleGUI::class):
                $this->ctrl->forwardCommand(new ilSrRuleGUI());
                break;

            default:
                throw new LogicException(self::class . " MUST never be executing class.");
        }

        $this->maybePrintGlobalTemplate();
    }

    /**
     * Returns the origin-type of the current request.
     *
     * The origin-type is determined by ilCtrl's call-history,
     * whereas the base-class is the defining property.
     * Note that this method currently DOES NOT support
     * @see IRoutine::ORIGIN_TYPE_EXTERNAL.
     *
     * @return int|null
     */
    public static function getOriginTypeFromRequest() : ?int
    {
        global $DIC;

        // fetch the first array-entry from ilCtrl's call-
        // history. This is always the base-class.
        $call_history = $DIC->ctrl()->getCallHistory();
        $base_class   = array_shift($call_history);
        $base_class   = strtolower($base_class['class']);

        // check the implementation class-name and return
        // the according origin-type.
        switch ($base_class) {
            case strtolower(ilUIPluginRouterGUI::class):
                return IRoutine::ORIGIN_TYPE_REPOSITORY;

            case strtolower(ilAdministrationGUI::class):
                return IRoutine::ORIGIN_TYPE_ADMINISTRATION;

            default:
                return null;
        }
    }

    /**
     * Returns a fully qualified link target for the given class and command.
     *
     * This method can be used whenever a link to a command class of this plugin
     * is made from outside ilCtrl's current scope (e.g. MenuProvider)
     *
     * @param string $class
     * @param string $cmd
     * @return string
     */
    public static function buildFullyQualifiedLinkTarget(string $class, string $cmd) : string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class, $class],
            $cmd
        );
    }

    /**
     * Helper function that prints the global template if the request comes
     * from a baseclass that doesn't print it by default.
     */
    protected function maybePrintGlobalTemplate() : void
    {
        if (IRoutine::ORIGIN_TYPE_REPOSITORY === self::getOriginTypeFromRequest()) {
            $this->global_template->printToStdout();
        }
    }
}