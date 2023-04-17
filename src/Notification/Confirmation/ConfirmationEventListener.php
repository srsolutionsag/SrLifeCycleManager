<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Event\IEventListener;
use srag\Plugins\SrLifeCycleManager\Event\IEvent;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationEventListener implements IEventListener
{
    /**
     * @var IConfirmationRepository
     */
    protected $confirmation_repository;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @var IRecipientRetriever
     */
    protected $recipient_retriever;

    public function __construct(
        IConfirmationRepository $confirmation_repository,
        INotificationSender $notification_sender,
        IRecipientRetriever $recipient_retriever
    ) {
        $this->confirmation_repository = $confirmation_repository;
        $this->notification_sender = $notification_sender;
        $this->recipient_retriever = $recipient_retriever;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::class;
    }

    /**
     * @inheritDoc
     */
    public function handle(IEvent $event): void
    {
        if (!$event instanceof RoutineEvent) {
            return;
        }

        $confirmation = $this->confirmation_repository->getByRoutineAndEvent(
            $event->getRoutine()->getRoutineId(),
            $event->getName()
        );

        if (null === $confirmation) {
            return;
        }

        if (!$event->isRepeatable() &&
            $this->confirmation_repository->hasObjectBeenNotified($confirmation, $event->getObject()->getRefId())
        ) {
            return;
        }

        $this->notification_sender->sendNotification($this->recipient_retriever, $confirmation, $event->getObject());
    }
}
