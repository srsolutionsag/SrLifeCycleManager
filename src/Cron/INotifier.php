<?php

namespace srag\Plugins\SrLifeCycleManager\Cron;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotifier
{
    public const DEFAULT_INTERVAL = 500;

    /**
     * Pings the cron-manager and loggs the given message occasionally.
     *
     * Defaults to 500, which means that the cron-manager will perform said
     * task every 500th call.
     *
     * Calling notify() does not influence this counter.
     */
    public function notifySometimes(string $message): void;

    /**
     * Pings the cron-manager and logg the given message every time.
     */
    public function notify(string $message): void;
}
