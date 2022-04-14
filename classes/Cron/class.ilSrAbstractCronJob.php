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

        return $this->result_builder
            ->success()
            ->message('Successfully terminated.')
            ->getResult()
        ;
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
}