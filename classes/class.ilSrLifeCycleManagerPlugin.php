<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

// This is the first class being loaded, therefore the plugin's
// autoloader can be included here.
require __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\Rule\Generator\DeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
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
     * @var Container
     */
    protected $dic;

    /**
     * Initializes the tool- and main-menu-provider and registers them.
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->dic = $DIC;
        $this->provider_collection
            ->setMainBarProvider(new ilSrMenuProvider($DIC, $this))
            ->setToolProvider(new ilSrToolProvider($DIC, $this))
        ;
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
            $this->getCronJobInstance(ilSrRoutineCronJob::class),
        ];
    }

    /**
     * @param string $a_job_id
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id) : ilCronJob
    {
        if (ilSrRoutineCronJob::class !== $a_job_id) {
            throw new LogicException("No cron-job instance for '$a_job_id' found.");
        }

        $repository = new ilSrLifeCycleManagerRepository(
            $this->dic->database(),
            $this->dic->rbac(),
            $this->dic->repositoryTree()
        );

        return new ilSrRoutineCronJob(
            new ilSrResultBuilder(),
            $this->dic['mail.mime.sender.factory']->system(),
            new DeletableObjectGenerator(
                $repository->getRepositoryObjects(1),
                new RequirementFactory($this->dic->database()),
                new AttributeFactory(),
                $repository
            ),
            $repository,
            $this,
            $this->dic->logger()->root(),
            $this->dic->ctrl()
        );
    }
}