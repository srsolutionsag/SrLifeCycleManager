<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\ConfirmationEventListener;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\SurveyAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\RessourceFactory;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\Observer;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\DI\Container;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerContainer
{
    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @var RepositoryFactory
     */
    protected $repository_factory;

    /**
     * @var DeletableObjectProvider
     */
    protected $deletable_object_provider;

    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var ConfirmationEventListener
     */
    protected $confirmation_event_listener;

    /**
     * @var ilSrRoutineJobFactory
     */
    protected $routine_job_factory;

    /**
     * @var IRecipientRetriever
     */
    protected $recipient_retriever;

    public function __construct(ilSrLifeCycleManagerPlugin $plugin, Container $dic)
    {
        $this->plugin = $plugin;
        $this->dic = $dic;
    }

    public function getRepositoryFactory(): RepositoryFactory
    {
        if (null === $this->repository_factory) {
            $this->abortIfDependenciesNotAvailable(['ilDB', 'tree', 'rbacreview']);

            $reminder_repository = new ilSrReminderRepository($this->dic->database());
            $whitelist_repository = new ilSrWhitelistRepository($this->dic->database());

            $this->repository_factory = new RepositoryFactory(
                new ilSrGeneralRepository($this->dic->database(), $this->dic->repositoryTree(), $this->dic->rbac()),
                new ilSrConfigRepository($this->dic->database(), $this->dic->rbac()),
                new ilSrRoutineRepository(
                    $reminder_repository,
                    $whitelist_repository,
                    $this->dic->database(),
                    $this->dic->repositoryTree()
                ),
                new ilSrAssignmentRepository($this->dic->database(), $this->dic->repositoryTree()),
                new ilSrRuleRepository($this->dic->database(), $this->dic->repositoryTree()),
                new ilSrConfirmationRepository($this->dic->database()),
                $reminder_repository,
                $whitelist_repository,
                new ilSrTokenRepository($this->dic->database())
            );
        }

        return $this->repository_factory;
    }

    public function getRoutineProvider(): RoutineProvider
    {
        if (null === $this->routine_provider) {
            $this->abortIfDependenciesNotAvailable(['ilDB']);

            $this->routine_provider = new RoutineProvider(
                new ComparisonFactory(
                    new RessourceFactory($this->dic->database()),
                    $this->getAttributeFactory()
                ),
                $this->getRepositoryFactory()->routine(),
                $this->getRepositoryFactory()->rule()
            );
        }

        return $this->routine_provider;
    }

    public function getDeletableObjectProvider(): DeletableObjectProvider
    {
        if (null === $this->deletable_object_provider) {
            $this->deletable_object_provider = new DeletableObjectProvider(
                $this->getRoutineProvider(),
                $this->getRepositoryFactory()->general()
            );
        }

        return $this->deletable_object_provider;
    }

    public function getAttributeFactory(): AttributeFactory
    {
        if (null === $this->attribute_factory) {
            $this->abortIfDependenciesNotAvailable(['refinery']);

            $this->attribute_factory = new AttributeFactory(
                new CommonAttributeFactory($this->dic->refinery()),
                new ObjectAttributeFactory(),
                new SurveyAttributeFactory(),
                new CourseAttributeFactory()
            );
        }

        return $this->attribute_factory;
    }

    public function getNotificationSender(): INotificationSender
    {
        if (null === $this->notification_sender) {
            $this->abortIfDependenciesNotAvailable(['ilCtrl', 'mail.mime.sender.factory']);

            /** @var $sender_factory ilMailMimeSenderFactory */
            $sender_factory = $this->dic['mail.mime.sender.factory'];

            $config = $this->getRepositoryFactory()->config()->get();

            $sender = (!empty(($sender = $config->getNotificationSenderAddress()))) ?
                $sender_factory->userByEmailAddress($sender) :
                $sender_factory->system();

            $this->notification_sender = new ilSrNotificationSender(
                $this->getRepositoryFactory()->reminder(),
                $this->getRepositoryFactory()->routine(),
                new ilSrWhitelistLinkGenerator(
                    $this->getRepositoryFactory()->token(),
                    $this->dic->ctrl()
                ),
                $sender,
                $config
            );
        }

        return $this->notification_sender;
    }

    public function getRoutineJobFactory(): ilSrRoutineJobFactory
    {
        if (null === $this->routine_job_factory) {
            $this->abortIfDependenciesNotAvailable(['ilLoggerFactory']);

            $this->routine_job_factory = new ilSrRoutineJobFactory(
                $this->getNotificationSender(),
                $this->getRecipientRetriever(),
                new ResultBuilder(
                    new ilCronJobResult()
                ),
                $this->getRepositoryFactory(),
                $this->getObserver(),
                $this->getDeletableObjectProvider(),
                $this->dic->logger()->root()
            );
        }

        return $this->routine_job_factory;
    }

    public function getConfirmationEventListener(): ConfirmationEventListener
    {
        if (null === $this->confirmation_event_listener) {
            $this->confirmation_event_listener = new ConfirmationEventListener(
                $this->getRepositoryFactory()->confirmation(),
                $this->getNotificationSender(),
                $this->getRecipientRetriever()
            );
        }

        return $this->confirmation_event_listener;
    }

    public function getRecipientRetriever(): IRecipientRetriever
    {
        if (null === $this->recipient_retriever) {
            $this->recipient_retriever = new ilSrRecipientRetriever();
        }

        return $this->recipient_retriever;
    }

    public function getTranslator(): ITranslator
    {
        return $this->plugin;
    }

    public function getObserver(): Observer
    {
        return Observer::getInstance();
    }

    protected function abortIfDependenciesNotAvailable(array $dependencies): void
    {
        foreach ($dependencies as $dependency) {
            if (!$this->dic->offsetExists($dependency)) {
                throw new LogicException("Dependency '$dependency' is not available in the current context.");
            }
        }
    }
}
