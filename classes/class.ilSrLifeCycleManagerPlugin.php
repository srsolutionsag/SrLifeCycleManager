<?php

require_once __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\DI\Container;

/**
 * Class ilSrLifeCycleManagerPlugin holds the (singleton) plugin instance.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The plugin instance is primarily used to handle I18N, because it provides a method
 * which translates language variables into the corresponding text contained in
 * '/../lang' directory.
 *
 * Besides that it provides cronjob instances, that are responsible for validating
 * rule-sets registered in the administration of this plugin, and applying them -
 * deleting (old) course objects that match an active routine's rules.
 */
class ilSrLifeCycleManagerPlugin extends ilCronHookPlugin implements ITranslator
{
    /**
     * @var string
     */
    public const PLUGIN_ID = 'srlcm';

    /**
     * @var string
     */
    public const PLUGIN_DIR = './Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/';

    /**
     * @var string
     */
    public const PLUGIN_NAME = 'SrLifeCycleManager';

    /**
     * @var array<string, ilCronJob>
     */
    protected $cron_job_instances = [];

    /**
     * @var self
     */
    private static $instance;

    /**
     * prevents multiple instances.
     */
    private function __clone() {}
    private function __wakeup() {}

    /**
     * ilSrLifeCycleManagerPlugin constructor (protected to prevent multiple instances).
     */
    protected function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->cron_job_instances = $this->safelyInitializeCronJobs($DIC);

        // register global-screen providers (for tools and main-menu entries)
        $this->provider_collection->setToolProvider(new ilSrToolProvider($DIC, $this));
        $this->provider_collection->setMainBarProvider(new ilSrMenuProvider($DIC, $this));
    }

    /**
     * @return string
     */
    public function getPluginDir() : string
    {
        return self::PLUGIN_DIR;
    }

    /**
     * @return string
     */
    public function getPluginId() : string
    {
        return self::PLUGIN_ID;
    }

    /**
     * @inheritDoc
     */
    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $a_job_id
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id) : ilCronJob
    {
        switch ($a_job_id) {
            case ilSrNotificationJob::class:
                return $this->cron_job_instances[ilSrNotificationJob::class];

            case ilSrRoutineJob::class:
                return $this->cron_job_instances[ilSrRoutineJob::class];

            default:
                throw new LogicException("Tried loading cron job '$a_job_id' which does not exist.");
        }
    }

    /**
     * @return ilCronJob[]
     */
    public function getCronJobInstances() : array
    {
        return $this->cron_job_instances;
    }

    /**
     * Helper function that initializes all cron-job instances of
     * this plugin, if the required dependencies are available.
     *
     * @return array<string, ilCronJob>
     */
    protected function safelyInitializeCronJobs(Container $dic) : array
    {
        if ($dic->offsetExists('ilDB') &&
            $dic->offsetExists('ilLoggerFactory') &&
            $dic->offsetExists('tree') &&
            $dic->offsetExists('rbacreview') &&
            $dic->offsetExists('ctrl') &&
            $dic->offsetExists('mail.mime.sender.factory')
        ) {
            $repository = new ilSrLifeCycleManagerRepository(
                $dic->database(),
                $dic->rbac(),
                $dic->repositoryTree()
            );

            $configuration = $repository->config()->get();

            return [
                ilSrNotificationJob::class => new ilSrNotificationJob(
                    $repository,
                    $dic->logger()->root(),
                    $configuration,
                    $dic['mail.mime.sender.factory']->system(),
                    $dic->ctrl()
                ),

                ilSrRoutineJob::class => new ilSrRoutineJob(
                    $repository,
                    $dic->logger()->root(),
                    $configuration
                ),
            ];
        }

        return [];
    }
}