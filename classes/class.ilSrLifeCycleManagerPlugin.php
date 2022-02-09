<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
final class ilSrLifeCycleManagerPlugin extends ilCronHookPlugin
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
    public function getPluginName()
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
     * returns all cronjob instances of this plugin.
     */
    public function getCronJobInstances()
    {
        // TODO: Implement getCronJobInstances() method.
    }

    /**
     * returns a single cronjob instance.
     *
     * @param $a_job_id
     */
    public function getCronJobInstance($a_job_id)
    {
        // TODO: Implement getCronJobInstance() method.
    }
}