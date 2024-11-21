<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRecipientRetriever
{
    /**
     * Must return an array of valid user-ids whom should be notified
     * when sending confirmations or reminders.
     *
     * @return int[]
     * @throws \LogicException if the recipients could not be gathered.
     */
    public function getRecipients(\ilObject $object): array;
}
