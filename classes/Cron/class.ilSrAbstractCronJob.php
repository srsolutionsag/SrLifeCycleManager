<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractCronJob extends ilCronJob
{
    /**
     * @var string prefix for all logger messages.
     */
    protected const LOGGER_PREFIX = '[SrLifeCycleManager] ';

    /**
     * @var ResultBuilder
     */
    protected $result_builder;

    /**
     * @var string[]
     */
    protected $summary = [];

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @param ResultBuilder $builder
     * @param ilLogger      $logger
     */
    public function __construct(ResultBuilder $builder, ilLogger $logger)
    {
        $this->result_builder = $builder;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function run() : ilCronJobResult
    {
        $this->result_builder->request()->trackTime();

        try {
            $this->execute();
        } catch (Throwable $throwable) {
            return $this->result_builder
                ->crash()
                ->message($throwable->getMessage() . $throwable->getTraceAsString())
                ->getResult()
            ;
        }

        $result = $this->result_builder
            ->success()
            ->message($this->getSummary())
            ->getResult()
        ;

        // displays an info-toast with the summary of the current cron-job
        // at the top of the cron-job administration page.
        if (!$this->isCLI()) {
            ilUtil::sendInfo($this->getSummary('<br />'), true);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return static::class;
    }

    /**
     * @inheritDoc
     */
    public function hasAutoActivation() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * This method MUST implement the actual cron-job.
     *
     * The execution has been wrapped by a catch clause to manage
     * possible crashes.
     */
    abstract protected function execute() : void;

    /**
     * Returns the summary glued together (each entry as a new line).
     *
     * @param string $line_break
     * @return string
     */
    protected function getSummary(string $line_break = PHP_EOL) : string
    {
        $message = 'Successfully terminated.';
        if (!empty($this->summary)) {
            $message .=
                $line_break .
                $line_break .
                implode($line_break, $this->summary)
            ;
        }

        return $message;
    }

    /**
     * @param string $summary
     */
    protected function addSummary(string $summary) : void
    {
        $this->summary[] = $summary;
    }

    /**
     * @param string $message
     */
    protected function info(string $message) : void
    {
        $this->logger->info(self::LOGGER_PREFIX . $message);
    }

    /**
     * @param string $message
     */
    protected function error(string $message) : void
    {
        $this->logger->error(self::LOGGER_PREFIX . $message);
    }

    /**
     * Returns whether the cron-job instance has been started via CLI.
     *
     * @return bool
     */
    protected function isCLI() : bool
    {
        return PHP_SAPI === 'cli';
    }
}
