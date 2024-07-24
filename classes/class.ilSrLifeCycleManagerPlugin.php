<?php

declare(strict_types=1);

// This is the first class being loaded, therefore the plugin's
// autoloader can be included here.
require __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\DI\Container;

/**
 * This class is the actual plugin object.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * It's primary responsibility is managing the plugins cron-jobs and loading
 * cron-job instances.
 *
 * The plugin object is mainly used as a translator though, because it implements
 * a translation method by default, that properly translates variables contained
 * in .lang files located in '/lang/' relative to the plugin root.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerPlugin extends ilCronHookPlugin implements ITranslator
{
    /**
     * @var string plugin-directory relative to the ILIAS-installation path.
     */
    public const PLUGIN_DIR = './Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/';

    /**
     * @var string plugin-id (MUST be the same as in plugin.php).
     */
    public const PLUGIN_ID = 'srlcm';

    /**
     * @var self|null
     */
    protected static $instance;

    /**
     * @var ilSrLifeCycleManagerContainer
     */
    protected $container;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * Initializes the global screen providers and event-listeners.
     */
    protected function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->container = new ilSrLifeCycleManagerContainer($this, $DIC);

        if ($DIC->offsetExists('global_screen') && $this->isActive()) {
            $this->provider_collection
                ->setMainBarProvider(new ilSrMenuProvider($DIC, $this))
                ->setToolProvider(new ilSrToolProvider($DIC, $this));

            $this->getContainer()->getEventSubject()->attach(
                $this->getContainer()->getConfirmationEventObserver()
            );
        }

        if ($DIC->offsetExists('ilDB')) {
            $this->db = $DIC->database();
        }

        self::$instance = $this;
    }

    /**
     * @inheritDoc
     */
    public function getPluginName(): string
    {
        return 'SrLifeCycleManager';
    }

    /**
     * @return ilCronJob[]
     */
    public function getCronJobInstances(): array
    {
        return [
            $this->getContainer()->getRoutineJobFactory()->standard(),
            $this->getContainer()->getRoutineJobFactory()->dryRun(),
        ];
    }

    /**
     * @param string $a_job_id
     */
    public function getCronJobInstance($a_job_id): ilCronJob
    {
        return $this->getContainer()->getRoutineJobFactory()->getJob($a_job_id);
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getContainer(): ilSrLifeCycleManagerContainer
    {
        return $this->container;
    }

    /**
     * Drops all database tables if the plugin is uninstalled.
     */
    protected function afterUninstall(): void
    {
        $this->db->dropTable('srlcm_configuration', false);
        $this->db->dropTable('srlcm_routine', false);
        $this->db->dropTable('srlcm_notification', false);
        $this->db->dropTable('srlcm_rule', false);
        $this->db->dropTable('srlcm_routine_rule', false);
        $this->db->dropTable('srlcm_whitelist', false);
        $this->db->dropTable('srlcm_notified_objects', false);
        $this->db->dropTable('srlcm_assigned_routine', false);
        $this->db->dropTable('srlcm_reminder', false);
        $this->db->dropTable('srlcm_tokens', false);
    }
}
