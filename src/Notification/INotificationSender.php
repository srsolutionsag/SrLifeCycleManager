<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Notification;

use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotificationSender
{
    /**
     * Sends the given notification (regardless of the type) to all administrators
     * of the given object.
     *
     * @param INotification $notification
     * @param ilObject      $object
     * @return ISentNotification
     */
    public function sendNotification(INotification $notification, ilObject $object) : ISentNotification;
}