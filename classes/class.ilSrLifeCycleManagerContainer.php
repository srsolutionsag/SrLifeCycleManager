<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\ConfirmationEventObserver;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant\ParticipantAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\RessourceFactory;
use srag\Plugins\SrLifeCycleManager\Routine\AffectingRoutineProvider;
use srag\Plugins\SrLifeCycleManager\Routine\ApplicabilityChecker;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\EventSubject;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\DI\Container;

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
     * @var AffectedObjectProvider
     */
    protected $affected_object_provider;

    /**
     * @var AffectingRoutineProvider
     */
    protected $affecting_routine_provider;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var ConfirmationEventObserver
     */
    protected $confirmation_event_observer;

    /**
     * @var ilSrRoutineJobFactory
     */
    protected $routine_job_factory;

    /**
     * @var IRecipientRetriever
     */
    protected $recipient_retriever;

    /**
     * @var ApplicabilityChecker
     */
    protected $applicapbililty_checker;

    /**
     * @var ilSrAccessHandler
     */
    protected $access_handler;

    /**
     * @var EventSubject
     */
    protected $event_subject;

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

    public function getAffectingRoutineProvider(): AffectingRoutineProvider
    {
        if (null === $this->affecting_routine_provider) {
            $this->abortIfDependenciesNotAvailable(['ilDB']);

            $this->affecting_routine_provider = new AffectingRoutineProvider(
                $this->getRepositoryFactory()->routine(),
                $this->getApplicapbililtyChecker()
            );
        }

        return $this->affecting_routine_provider;
    }

    public function getAffectedObjectProvider(): AffectedObjectProvider
    {
        if (null === $this->affected_object_provider) {
            $this->affected_object_provider = new AffectedObjectProvider(
                $this->getRepositoryFactory()->assignment(),
                $this->getRepositoryFactory()->general(),
                $this->getRepositoryFactory()->routine(),
                $this->getApplicapbililtyChecker(),
            );
        }

        return $this->affected_object_provider;
    }

    public function getAttributeFactory(): AttributeFactory
    {
        if (null === $this->attribute_factory) {
            $this->abortIfDependenciesNotAvailable(['refinery']);

            $this->attribute_factory = new AttributeFactory(
                new CommonAttributeFactory($this->dic->refinery()),
                new ParticipantAttributeFactory(),
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
                $this->getRepositoryFactory()->general(),
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
            $this->abortIfDependenciesNotAvailable(['ilLoggerFactory', 'cron.manager']);

            $this->routine_job_factory = new ilSrRoutineJobFactory(
                $this->getNotificationSender(),
                $this->getRecipientRetriever(),
                $this->getRepositoryFactory(),
                $this->getAffectedObjectProvider(),
                $this->getEventSubject(),
                new ResultBuilder(new ilCronJobResult()),
                new ilStrictCliCronManager($this->dic->cron()->manager()),
                $this->dic->logger()->root(),
                // cron-jobs are run in both CLI and web context, so we need to check if the template is available.
                ($this->dic->offsetExists('tpl')) ? $this->dic->ui()->mainTemplate() : null
            );
        }

        return $this->routine_job_factory;
    }

    public function getConfirmationEventObserver(): ConfirmationEventObserver
    {
        if (null === $this->confirmation_event_observer) {
            $this->confirmation_event_observer = new ConfirmationEventObserver(
                $this->getRepositoryFactory()->confirmation(),
                $this->getNotificationSender(),
                $this->getRecipientRetriever()
            );
        }

        return $this->confirmation_event_observer;
    }

    public function getRecipientRetriever(): IRecipientRetriever
    {
        if (null === $this->recipient_retriever) {
            $this->recipient_retriever = new ilSrRecipientRetriever();
        }

        return $this->recipient_retriever;
    }

    public function getApplicapbililtyChecker(): ApplicabilityChecker
    {
        if (null === $this->applicapbililty_checker) {
            $this->applicapbililty_checker = new ApplicabilityChecker(
                new ComparisonFactory(
                    new RessourceFactory($this->dic->database()),
                    $this->getAttributeFactory()
                ),
                $this->getRepositoryFactory()->rule()
            );
        }

        return $this->applicapbililty_checker;
    }

    public function getEventSubject(): EventSubject
    {
        if (null === $this->event_subject) {
            $this->event_subject = new EventSubject();
        }

        return $this->event_subject;
    }

    public function getAccessHandler(): ilSrAccessHandler
    {
        if (null === $this->access_handler) {
            $this->abortIfDependenciesNotAvailable(['rbacreview', 'ilUser']);

            $this->access_handler = new ilSrAccessHandler(
                $this->dic->rbac(),
                $this->getRepositoryFactory()->general(),
                $this->getRepositoryFactory()->config()->get(),
                $this->dic->user()
            );
        }

        return $this->access_handler;
    }

    public function getTranslator(): ITranslator
    {
        return $this->plugin;
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
