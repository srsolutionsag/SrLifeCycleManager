<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ilSrAbstractCronJob extends ilCronJob
{
    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilLogger                       $logger
     * @param IConfig                        $config
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilLogger $logger,
        IConfig $config
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->config = $config;
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
        return true;
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
        return 1;
    }
}