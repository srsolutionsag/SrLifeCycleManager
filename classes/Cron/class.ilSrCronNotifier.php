<?php

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

    /**
     * @var ilCronManager
     */
    protected $cron_manager;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $cron_job_id;

    /**
     * @var int
     */
    protected $interval;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @throws LogicException if $interval is not a positive integer
     */
    public function __construct(
        ilCronManager $cron_manager,
        ilLogger $logger,
        string $cron_job_id,
        int $interval = self::DEFAULT_INTERVAL
    ) {
        if (0 > $interval) {
            throw new LogicException('Interval must be a positive integer.');
        }

        $this->cron_manager = $cron_manager;
        $this->logger = $logger;
        $this->cron_job_id = $cron_job_id;
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
