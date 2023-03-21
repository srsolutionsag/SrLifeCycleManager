<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\Observer;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;

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
     * @var Observer
     */
    protected $event_observer;

    /**
     * @var DeletableObjectProvider
     */
    protected $objects_provider;

    /**
     * @var ilLogger
     */
    protected $logger;

    public function __construct(
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever,
        ResultBuilder $result_builder,
        RepositoryFactory $repositories,
        Observer $event_observer,
        DeletableObjectProvider $objects_provider,
        ilLogger $logger
    ) {
        $this->notification_sender = $notification_sender;
        $this->recipient_retriever = $recipient_retriever;
        $this->result_builder = $result_builder;
        $this->repository = $repositories;
        $this->event_observer = $event_observer;
        $this->objects_provider = $objects_provider;
        $this->logger = $logger;
    }

    /**
     * @param string $job_id
     * @return ilCronJob
     */
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

    /**
     * @return ilSrRoutineCronJob
     */
    public function standard(): ilSrRoutineCronJob
    {
        return new ilSrRoutineCronJob(
            $this->notification_sender,
            $this->recipient_retriever,
            $this->objects_provider,
            $this->result_builder,
            $this->event_observer,
            $this->repository->reminder(),
            $this->repository->token(),
            $this->repository->whitelist(),
            $this->repository->general(),
            $this->logger
        );
    }

    /**
     * @return ilSrDryRoutineCronJob
     */
    public function dryRun(): ilSrDryRoutineCronJob
    {
        return new ilSrDryRoutineCronJob(
            $this->notification_sender,
            $this->recipient_retriever,
            $this->objects_provider,
            $this->result_builder,
            $this->event_observer,
            $this->repository->reminder(),
            $this->repository->token(),
            $this->repository->whitelist(),
            $this->repository->general(),
            $this->logger
        );
    }
}
