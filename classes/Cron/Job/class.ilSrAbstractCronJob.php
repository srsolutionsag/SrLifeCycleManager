<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\ITranslator;
use srag\Plugins\SrLifeCycleManager\IRepository;

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
     * @var ilSrCronJobResultBuilder
     */
    protected $result_builder;

    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @param IRepository              $repository
     * @param ITranslator              $translator
     * @param ilLogger                 $logger
     * @param ilSrCronJobResultBuilder $builder
     */
    public function __construct(
        ilSrCronJobResultBuilder $builder,
        IRepository $repository,
        ITranslator $translator,
        ilLogger $logger
    ) {
        $this->result_builder = $builder;
        $this->repository = $repository;
        $this->translator = $translator;
        $this->logger = $logger;
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
     * @inheritDoc
     */
    abstract public function run() : ilCronJobResult;

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