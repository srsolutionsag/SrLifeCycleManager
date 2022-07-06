<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Event\IObserver;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineJobFactory
{
    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var ResultBuilder
     */
    protected $result_builder;

    /**
     * @var IReminderRepository
     */
    protected $reminder_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IObserver
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

    /**
     * @param INotificationSender     $notification_sender
     * @param ResultBuilder           $result_builder
     * @param IReminderRepository     $reminder_repository
     * @param IRoutineRepository      $routine_repository
     * @param IObserver               $event_observer
     * @param DeletableObjectProvider $objects_provider
     * @param ilLogger                $logger
     */
    public function __construct(
        INotificationSender $notification_sender,
        ResultBuilder $result_builder,
        IReminderRepository $reminder_repository,
        IRoutineRepository $routine_repository,
        IObserver $event_observer,
        DeletableObjectProvider $objects_provider,
        ilLogger $logger
    ) {
        $this->notification_sender = $notification_sender;
        $this->result_builder = $result_builder;
        $this->reminder_repository = $reminder_repository;
        $this->routine_repository = $routine_repository;
        $this->event_observer = $event_observer;
        $this->objects_provider = $objects_provider;
        $this->logger = $logger;
    }

    /**
     * @param string $job_id
     * @return ilCronJob
     */
    public function getJob(string $job_id) : ilCronJob
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
    public function standard() : ilSrRoutineCronJob
    {
        return new ilSrRoutineCronJob(
            $this->notification_sender,
            $this->objects_provider,
            $this->result_builder,
            $this->event_observer,
            $this->reminder_repository,
            $this->routine_repository,
            $this->logger
        );
    }

    /**
     * @return ilSrDryRoutineCronJob
     */
    public function dryRun() : ilSrDryRoutineCronJob
    {
        return new ilSrDryRoutineCronJob(
            $this->notification_sender,
            $this->objects_provider,
            $this->result_builder,
            $this->event_observer,
            $this->reminder_repository,
            $this->routine_repository,
            $this->logger
        );
    }
}
