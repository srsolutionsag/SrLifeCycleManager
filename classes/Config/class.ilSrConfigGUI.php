<?php

use ILIAS\DI\HTTPServices;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Class ilSrConfigGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilSrConfigGUI extends ilSrCourseManagerGUI
{
    /**
     *  ilSrConfigGUI lang vars
     */
    protected const MSG_SUCCESS = 'msg_cnf_success';
    protected const MSG_FAILURE = 'msg_cnf_failure';

    /**
     * tab ids (also used as lang vars)
     */
    protected const TAB_SETTINGS = 'tab_settings';
    protected const TAB_RULES    = 'tab_rules';

    /**
     * ilSrConfigGUI commands
     */
    public const CMD_CONFIG_INDEX  = 'configure';
    public const CMD_CONFIG_SEARCH = 'search';
    public const CMD_CONFIG_SAVE   = 'save';

    /**
     * ilSrConfigGUI query params
     */
    public const TAXONOMY_QUERY_PARAM = 'tax_id';

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * ilSrConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();

        parent::__construct();
    }

    /**
     * displays the plugin configuration form.
     */
    private function configure() : void
    {
        $form_gui = new ilSrConfigFormGUI();
        $this->tpl->setContent($form_gui->getHTML());
    }

    /**
     * sends an http response with all available sub taxonomies for the selected taxonomy.
     *
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    private function search() : void
    {
        $sub_taxonomies = [];
        $tax_id = $this->http->request()->getQueryParams()[self::TAXONOMY_QUERY_PARAM];
        if (null !== $tax_id) {
            $sub_taxonomies = $this->repository->getSubTaxonomiesForTaxonomy((int) $tax_id);
        }

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody(Streams::ofString(json_encode($sub_taxonomies)))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * processes the configuration form and redirects success/failure.
     *
     * @throws arException
     */
    private function save() : void
    {
        $form_gui = new ilSrConfigFormGUI();
        $msg = $form_gui->saveConfig();
        if (null === $msg) {
            ilUtil::sendSuccess($this->plugin->txt(self::MSG_SUCCESS), true);
        } else {
            ilUtil::sendFailure($this->plugin->txt($msg), true);
        }

        $this->repeat();
    }

    /**
     * redirects back to the plugin configuration form.
     */
    private function repeat() : void
    {
        $this->ctrl->redirectByClass(
            [ilSrCourseManagerGUI::class, self::class],
            self::CMD_CONFIG_INDEX
        );
    }

    /**
     * adds configuration tabs to the page.
     */
    protected function setupTabs(string $active_tab = null) : void
    {
        $this->tabs->addTab(
            self::TAB_SETTINGS,
            $this->plugin->txt(self::TAB_SETTINGS),
            $this->ctrl->getLinkTargetByClass(
                [ilSrCourseManagerGUI::class, self::class],
                self::CMD_CONFIG_INDEX
            )
        );

        $this->tabs->addTab(
            self::TAB_RULES,
            $this->plugin->txt(self::TAB_RULES),
            $this->ctrl->getLinkTargetByClass(
                [ilSrCourseManagerGUI::class, ilSrRuleGUI::class],
                ilSrRuleGUI::CMD_RULE_INDEX
            )
        );

        if (null !== $active_tab) {
            $this->tabs->activateTab($active_tab);
        }
    }

    /**
     * dispatches and performs the given command.
     */
    public function executeCommand() : void
    {
        $this->setupTabs(self::TAB_SETTINGS);
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_CONFIG_INDEX:
            case self::CMD_CONFIG_SEARCH:
            case self::CMD_CONFIG_SAVE:
                if (ilSrCourseManagerAccess::isCurrentUserAdmin()) {
                    $this->$cmd();
                }
                break;
            default:
                $this->showErrorMessage(self::MSG_PERMISSION_DENIED);
                break;
        }
    }
}