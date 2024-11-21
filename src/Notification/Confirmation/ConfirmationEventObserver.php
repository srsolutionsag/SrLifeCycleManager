<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineEvent;
use srag\Plugins\SrLifeCycleManager\Event\IEventObserver;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationEventObserver implements IEventObserver
{
    public function __construct(
        protected IConfirmationRepository $confirmation_repository,
        protected INotificationSender $notification_sender,
        protected IRecipientRetriever $recipient_retriever
    ) {
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
    public function update(string $event, $data = null): void
    {
        if (!$data instanceof AffectedObject) {
            return;
        }

        $confirmation = $this->confirmation_repository->getByRoutineAndEvent(
            $data->getRoutine()->getRoutineId(),
            $event
        );

        if (null === $confirmation) {
            return;
        }

        if (!$this->isEventRepeatable($event) &&
            $this->confirmation_repository->hasObjectBeenNotified($confirmation, $data->getObject()->getRefId())
        ) {
            return;
        }

        $this->notification_sender->sendNotification($this->recipient_retriever, $confirmation, $data->getObject());
    }

    /**
     * This observer must know about repeating events, because it should only
     * notify a user once for any other event.
     */
    protected function isEventRepeatable(string $event): bool
    {
        return (
            IRoutineEvent::EVENT_POSTPONE === $event ||
            IRoutineEvent::EVENT_OPT_OUT === $event
        );
    }
}
