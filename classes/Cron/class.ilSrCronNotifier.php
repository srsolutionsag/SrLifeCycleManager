<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Cron\INotifier;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrCronNotifier implements INotifier
{
    /**
     * @var string prefix for all logger messages.
     */
    protected const LOGGER_PREFIX = '[SrLifeCycleManager] ';

    protected int $interval;

    protected int $counter = 0;

    /**
     * @throws LogicException if $interval is not a positive integer
     */
    public function __construct(
        protected \ilCronManager $cron_manager,
        protected \ilLogger $logger,
        protected string $cron_job_id,
        int $interval = self::DEFAULT_INTERVAL
    ) {
        if (0 > $interval) {
            throw new LogicException('Interval must be a positive integer.');
        }
        $this->interval = $interval;
    }

    /**
     * @inheritDoc
     */
    public function notifySometimes(string $message): void
    {
        if (0 === $this->counter % $this->interval) {
            $this->notify($message);
            $this->counter = 0;
        }

        $this->counter++;
    }

    /**
     * @inheritDoc
     */
    public function notify(string $message): void
    {
        $this->logger->info(self::LOGGER_PREFIX . $message);
        $this->cron_manager->ping($this->cron_job_id);
    }
}
