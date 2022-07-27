<?php

namespace srag\Plugins\SrLifeCycleManager\Notification\Confirmation;

use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfirmation extends ISentNotification
{
    // IConfirmation attributes:
    public const F_EVENT = 'event';

    /**
     * @return string
     */
    public function getEvent() : string;

    /**
     * @param string $event
     * @return IConfirmation
     */
    public function setEvent(string $event) : IConfirmation;
}