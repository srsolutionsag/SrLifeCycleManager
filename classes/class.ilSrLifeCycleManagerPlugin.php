<?php

/**
 * Class ilSrLifeCycleManagerPlugin holds a singleton plugin instance.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilSrLifeCycleManagerPlugin extends ilCronHookPlugin
{
    /**
     * @var string
     */
    public const PLUGIN_ID = 'srlcm';

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
        // register the plugin tools provider
        $this->provider_collection->setToolProvider(
            new ilSrLifeCycleManagerToolsProvider($DIC, $this)
        );
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
     *
     */
    public function getCronJobInstances()
    {
        // TODO: Implement getCronJobInstances() method.
    }

    /**
     *
     */
    public function getCronJobInstance($a_job_id)
    {
        // TODO: Implement getCronJobInstance() method.
    }
}