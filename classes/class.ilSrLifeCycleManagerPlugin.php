<?php

require_once __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\ITranslator;

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
     * @var self
     */
    private static $instance;

    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * @var ilLogger
     */
    protected $logger;

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

        // register global-screen providers (for tools and main-menu entries)
        $this->provider_collection->setToolProvider(new ilSrToolProvider($DIC, $this));
        $this->provider_collection->setMainBarProvider(new ilSrMenuProvider($DIC, $this));

        // Safely initializes dependencies, as this class will also be
        // loaded for CLI operations where they might not be available.
        if ($DIC->offsetExists('ilDB') &&
            $DIC->offsetExists('ilLoggerFactory') &&
            $DIC->offsetExists('tree') &&
            $DIC->offsetExists('rbacreview')
        ) {
            $this->logger = $DIC->logger()->root();
            $this->repository = new ilSrLifeCycleManagerRepository(
                $DIC->database(),
                $DIC->rbac(),
                $DIC->repositoryTree()
            );
        }
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
        if (!isset(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    /**
     * @return ilCronJob[]
     */
    public function getCronJobInstances() : array
    {
        return [
            $this->loadJobInstance(ilSrNotificationJob::class),
            $this->loadJobInstance(ilSrRoutineJob::class),
        ];
    }

    /**
     * @param string $a_job_id
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id) : ilCronJob
    {
        switch ($a_job_id) {
            case ilSrNotificationJob::class:
                return $this->loadJobInstance(ilSrNotificationJob::class);

            case ilSrRoutineJob::class:
                return $this->loadJobInstance(ilSrRoutineJob::class);

            default:
                throw new LogicException("Tried loading cron job '$a_job_id' which does not exist.");
        }
    }

    /**
     * @param string $class_name
     * @return ilCronJob
     */
    protected function loadJobInstance(string $class_name) : ilCronJob
    {
        return new $class_name(
            $this->repository,
            $this->logger
        );
    }
}