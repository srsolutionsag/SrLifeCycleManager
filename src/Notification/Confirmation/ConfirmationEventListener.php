<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Event\IEventListener;
use srag\Plugins\SrLifeCycleManager\Event\IEvent;

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
     * @param IConfirmationRepository $confirmation_repository
     * @param INotificationSender     $notification_sender
     */
    public function __construct(
        IConfirmationRepository $confirmation_repository,
        INotificationSender $notification_sender
    ) {
        $this->confirmation_repository = $confirmation_repository;
        $this->notification_sender = $notification_sender;
    }

    /**
     * @inheritDoc
     */
    public function handle(IEvent $event) : void
    {
        if (!$event instanceof RoutineEvent) {
            return;
        }

        $confirmation = $this->confirmation_repository->getByRoutineAndEvent(
            $event->getRoutine()->getRoutineId(),
            $event->getAction()
        );

        if (null === $confirmation) {
            return;
        }

        if (!$event->isRepeatable() &&
            $this->confirmation_repository->hasObjectBeenNotified($confirmation, $event->getObject()->getRefId())
        ) {
            return;
        }

        $this->notification_sender->sendNotification($confirmation, $event->getObject());
    }
}
