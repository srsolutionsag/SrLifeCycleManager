<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Event\EventSubject;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineJobFactory
{
    protected INotificationSender $notification_sender;

    protected IRecipientRetriever $recipient_retriever;

    protected ResultBuilder $result_builder;

    protected RepositoryFactory $repository;

    protected EventSubject $event_subject;

    protected AffectedObjectProvider $affected_objects_provider;

    protected \ilCronManager $cron_manager;

    protected \ilLogger $logger;

    protected ?\ilGlobalTemplateInterface $template;

    public function __construct(
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever,
        RepositoryFactory $repositories,
        AffectedObjectProvider $affected_object_provider,
        EventSubject $event_subject,
        ResultBuilder $result_builder,
        ilCronManager $cron_manager,
        ilLogger $logger,
        ilGlobalTemplateInterface $template = null
    ) {
        $this->notification_sender = $notification_sender;
        $this->recipient_retriever = $recipient_retriever;
        $this->result_builder = $result_builder;
        $this->repository = $repositories;
        $this->event_subject = $event_subject;
        $this->affected_objects_provider = $affected_object_provider;
        $this->cron_manager = $cron_manager;
        $this->logger = $logger;
        $this->template = $template;
    }

    public function getJob(string $job_id): ilCronJob
    {
        switch ($job_id) {
            case ilSrRoutineCronJob::class:
                return $this->standard();

            case ilSrDryRoutineCronJob::class:
                return $this->dryRun();

            default:
                throw new OutOfBoundsException("Cron job '$job_id' was not found.");
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
            $this->getNotifier(ilSrRoutineCronJob::class),
            $this->template,
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
            $this->getNotifier(ilSrDryRoutineCronJob::class),
            $this->template,
        );
    }

    protected function getNotifier(string $job_id): INotifier
    {
        return new ilSrCronNotifier($this->cron_manager, $this->logger, $job_id);
    }
}
