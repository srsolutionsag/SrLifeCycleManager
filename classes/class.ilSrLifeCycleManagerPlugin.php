<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

// This is the first class being loaded, therefore the plugin's
// autoloader can be included here.
require __DIR__ . '/../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\ConfirmationEventListener;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\IEventListener;
use srag\Plugins\SrLifeCycleManager\Event\IObserver;
use srag\Plugins\SrLifeCycleManager\Event\IEvent;
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
class ilSrLifeCycleManagerPlugin extends ilCronHookPlugin implements IObserver, ITranslator
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
     * @var IEventListener[]
     */
    protected $event_listeners = [];

    /**
     * @var ilSrRoutineJobFactory
     */
    protected $job_factory;

    /**
     * Initializes the global screen providers and event-listeners.
     */
    protected function __construct()
    {
        global $DIC;
        parent::__construct();

        // only initialize dependencies if the plugin is active, there might be
        // installation errors because db tables don't exist yet.
        if ($this->isActive()) {
            $this->safelyInitDependencies($DIC);
        }

        self::$instance = $this;
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
    public function register(IEventListener $listener) : IObserver
    {
        $this->event_listeners[] = $listener;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function broadcast(IEvent $event) : IObserver
    {
        foreach ($this->event_listeners as $listener) {
            $listener->handle($event);
        }

        return $this;
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
            $this->job_factory->standard(),
            $this->job_factory->dryRun(),
        ];
    }

    /**
     * @param string $a_job_id
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id) : ilCronJob
    {
        return $this->job_factory->getJob($a_job_id);
    }

    /**
     * Wraps the initialization of plugin-dependencies to ensure compatibility with
     * ILIAS>=7 where the setup-mechanism will require an instance of this object
     * but cannot provide all ILIAS dependencies.
     *
     * @TODO: this should probably be replaced by a local DIC implementation of sorts.
     *
     * @param Container $dic
     */
    protected function safelyInitDependencies(Container $dic) : void
    {
        $required_offsets = [
            'mail.mime.sender.factory', 'ilLoggerFactory', 'global_screen', 'rbacreview', 'ilCtrl', 'ilDB', 'tree',
        ];

        foreach ($required_offsets as $offset) {
            if (!$dic->offsetExists($offset)) {
                return;
            }
        }

        $reminder_repository = new ilSrReminderRepository($dic->database());
        $whitelist_repository = new ilSrWhitelistRepository($dic->database());

        $repository_factory = new RepositoryFactory(
            new ilSrGeneralRepository($dic->database(), $dic->repositoryTree(), $dic->rbac()),
            new ilSrConfigRepository($dic->database(), $dic->rbac()),
            new ilSrRoutineRepository(
                $reminder_repository,
                $whitelist_repository,
                $dic->database(),
                $dic->repositoryTree()
            ),
            new ilSrAssignmentRepository($dic->database(), $dic->repositoryTree()),
            new ilSrRuleRepository($dic->database(), $dic->repositoryTree()),
            new ilSrConfirmationRepository($dic->database()),
            $reminder_repository,
            $whitelist_repository,
            new ilSrTokenRepository($dic->database())
        );

        /** @var $sender_factory ilMailMimeSenderFactory */
        $sender_factory = $dic['mail.mime.sender.factory'];

        $config = $repository_factory->config()->get();
        $sender = (!empty(($sender = $config->getNotificationSenderAddress()))) ?
            $sender_factory->userByEmailAddress($sender) :
            $sender_factory->system()
        ;

        $notification_sender = new ilSrNotificationSender(
            $repository_factory->reminder(),
            $repository_factory->routine(),
            new ilSrWhitelistLinkGenerator(
                $repository_factory->token(),
                $dic->ctrl()
            ),
            $sender,
            $config
        );

        $deletable_object_provider = new DeletableObjectProvider(
            new RoutineProvider(
                new ComparisonFactory(
                    new RequirementFactory($dic->database()),
                    new AttributeFactory()
                ),
                $repository_factory->routine(),
                $repository_factory->rule()
            ),
            $repository_factory->general()
        );

        $this->job_factory = new ilSrRoutineJobFactory(
            $notification_sender,
            new ResultBuilder(
                new ilCronJobResult()
            ),
            $repository_factory,
            $this,
            $deletable_object_provider,
            $dic->logger()->root()
        );

        $this->provider_collection
            ->setMainBarProvider(new ilSrMenuProvider($dic, $this))
            ->setToolProvider(new ilSrToolProvider($dic, $this))
        ;

        $this->register(new ConfirmationEventListener(
            $repository_factory->confirmation(),
            $notification_sender
        ));
    }
}
