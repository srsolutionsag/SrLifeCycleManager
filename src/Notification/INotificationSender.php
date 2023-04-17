<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationSender
{
    /**
     * Sends the given notification (regardless of the type) to the corresponding
     * recipients of the given object.
     *
     * Note that this method should also mark the notification as sent, hence it
     * should add an according with: @see INotificationRepository::markObjectAsNotified()
     */
    public function sendNotification(
        IRecipientRetriever $recipient_retriever,
        INotification $notification,
        ilObject $object
    ): ISentNotification;
}
