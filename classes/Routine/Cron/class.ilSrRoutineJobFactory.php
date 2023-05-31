<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Event\EventSubject;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;
use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineJobFactory
{
    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var IRecipientRetriever
     */
    protected $recipient_retriever;

    /**
     * @var ResultBuilder
     */
    protected $result_builder;

    /**
     * @var RepositoryFactory
     */
    protected $repository;

    /**
     * @var EventSubject
     */
    protected $event_subject;

    /**
     * @var AffectedObjectProvider
     */
    protected $affected_objects_provider;

    /**
     * @var ilLogger
     */
    protected $logger;

    public function __construct(
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever,
        RepositoryFactory $repositories,
        AffectedObjectProvider $affected_object_provider,
        EventSubject $event_subject,
        ResultBuilder $result_builder,
        ilLogger $logger
    ) {
        $this->notification_sender = $notification_sender;
        $this->recipient_retriever = $recipient_retriever;
        $this->result_builder = $result_builder;
        $this->repository = $repositories;
        $this->event_subject = $event_subject;
        $this->affected_objects_provider = $affected_object_provider;
        $this->logger = $logger;
    }

    public function getJob(string $job_id): ilCronJob
    {
        switch ($job_id) {
            case ilSrRoutineCronJob::class:
                return $this->standard();

            case ilSrDryRoutineCronJob::class:
                return $this->dryRun();

            default:
                throw new LogicException("Cron job '$job_id' was not found.");
        }
    }

    public function standard(): ilSrRoutineCronJob
    {
        return new ilSrRoutineCronJob(
            $this->notification_sender,
            $this->recipient_retriever,
            $this->repository->routine(),
            $this->repository->reminder(),
            $this->repository->token(),
            $this->repository->whitelist(),
            $this->repository->general(),
            $this->affected_objects_provider,
            $this->event_subject,
            $this->result_builder,
            $this->getNotifier(ilSrRoutineCronJob::class)
        );
    }

    public function dryRun(): ilSrDryRoutineCronJob
    {
        return new ilSrDryRoutineCronJob(
            $this->notification_sender,
            $this->recipient_retriever,
            $this->repository->routine(),
            $this->repository->reminder(),
            $this->repository->token(),
            $this->repository->whitelist(),
            $this->repository->general(),
            $this->affected_objects_provider,
            $this->event_subject,
            $this->result_builder,
            $this->getNotifier(ilSrDryRoutineCronJob::class)
        );
    }

    protected function getNotifier(string $job_id): INotifier
    {
        return new ilSrCronNotifier($this->logger, $job_id);
    }
}
