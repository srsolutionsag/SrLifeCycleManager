<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

// This is the first class being loaded, therefore the plugin's
// autoloader can be included here.
require __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\DI\Container;

/**
 * This class is the actual plugin object.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
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
     * @var string plugin-id (MUST be the same as in plugin.php).
     */
    public const PLUGIN_ID = 'srlcm';

    /**
     * @var string plugin-directory relative to the ILIAS-installation path.
     */
    public const PLUGIN_DIR = './Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/';

    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var ilSrCronJobFactory
     */
    protected $cron_job_factory;

    /**
     * Initializes the global screen providers and cron job factory.
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->safelyInitjobFactory($DIC);
        $this->safelyInitProviders($DIC);
    }

    /**
     * Returns an instance of the plugin object.
     *
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
     * @inheritDoc
     */
    public function getPluginName() : string
    {
        return 'SrLifeCycleManager';
    }

    /**
     * @return ilCronJob[]
     */
    public function getCronJobInstances() : array
    {
        return [
            $this->cron_job_factory->routine(),
            $this->cron_job_factory->dryRoutine(),
        ];
    }

    /**
     * @param string $a_job_id
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id) : ilCronJob
    {
        return $this->cron_job_factory->getCronJob($a_job_id);
    }

    /**
     * Wraps the initialization of the cron job factory in order to
     * keep compatibility with new setup-features where dependencies
     * might not be available.
     *
     * @param Container $dic
     */
    protected function safelyInitJobFactory(Container $dic) : void
    {
        if ($dic->offsetExists('mail.mime.sender.factory') &&
            $dic->offsetExists('ilDB') &&
            $dic->offsetExists('tree') &&
            $dic->offsetExists('ilLoggerFactory') &&
            $dic->offsetExists('rbacreview') &&
            $dic->offsetExists('ilCtrl') &&
            $dic->offsetExists('ilUser')
        ) {
            $this->cron_job_factory = new ilSrCronJobFactory(
                $dic['mail.mime.sender.factory'],
                $dic->database(),
                $dic->repositoryTree(),
                $dic->logger()->root(),
                $dic->rbac(),
                $dic->ctrl(),
                $dic->user()
            );
        }
    }

    /**
     * Wraps the initialization of the GS providers in order to
     * keep compatibility with new setup-features where dependencies
     * might not be available.
     *
     * @param Container $dic
     * @return void
     */
    protected function safelyInitProviders(Container $dic) : void
    {
        if ($dic->offsetExists('global_screen')) {
            $this->provider_collection
                ->setMainBarProvider(new ilSrMenuProvider($dic, $this))
                ->setToolProvider(new ilSrToolProvider($dic, $this))
            ;
        }
    }
}
