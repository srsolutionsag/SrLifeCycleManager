<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Rule\Generator\DeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\IRepository;
use ILIAS\DI\RBACServices;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrCronJobFactory
{
    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var RBACServices
     */
    protected $rbac;

    /**
     * @var ilMailMimeSenderFactory
     */
    protected $mail_factory;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilMailMimeSenderFactory $mail_factory
     * @param ilDBInterface           $database
     * @param ilTree                  $tree
     * @param ilLogger                $logger
     * @param RBACServices            $rbac
     * @param ilCtrl                  $ctrl
     */
    public function __construct(
        ilMailMimeSenderFactory $mail_factory,
        ilDBInterface $database,
        ilTree $tree,
        ilLogger $logger,
        RBACServices $rbac,
        ilCtrl $ctrl
    ) {
        $this->mail_factory = $mail_factory;
        $this->database = $database;
        $this->tree = $tree;
        $this->logger = $logger;
        $this->rbac = $rbac;
        $this->ctrl = $ctrl;

        $this->repository = new ilSrLifeCycleManagerRepository(
            $database,
            $rbac,
            $tree
        );
    }

    /**
     * @param string $cron_job_id
     * @return ilCronJob
     */
    public function getCronJob(string $cron_job_id) : ilCronJob
    {
        switch ($cron_job_id) {
            case ilSrRoutineCronJob::class:
                return $this->routine();

            case ilSrDryRoutineCronJob::class:
                return $this->dryRoutine();

            case ilSrCleanUpCronJob::class:
                return $this->cleanUp();

            default:
                throw new LogicException("Cron job '$cron_job_id' was not found.");
        }
    }

    /**
     * @return ilSrRoutineCronJob
     */
    public function routine() : ilSrRoutineCronJob
    {
        return new ilSrRoutineCronJob(
            new ilSrNotificationSender(
                $this->repository->notification(),
                $this->mail_factory->system(),
                $this->ctrl
            ),
            new DeletableObjectGenerator(
                new RequirementFactory($this->database),
                new AttributeFactory(),
                $this->repository->routine(),
                $this->repository->rule(),
                $this->repository->getRepositoryObjects()
            ),
            new ResultBuilder(new ilCronJobResult()),
            $this->repository->notification(),
            $this->repository->routine(),
            $this->repository->whitelist(),
            $this->logger
        );
    }

    /**
     * @return ilSrDryRoutineCronJob
     */
    public function dryRoutine() : ilSrDryRoutineCronJob
    {
        return new ilSrDryRoutineCronJob(
            new ilSrNotificationSender(
                $this->repository->notification(),
                $this->mail_factory->system(),
                $this->ctrl
            ),
            new DeletableObjectGenerator(
                new RequirementFactory($this->database),
                new AttributeFactory(),
                $this->repository->routine(),
                $this->repository->rule(),
                $this->repository->getRepositoryObjects()
            ),
            new ResultBuilder(new ilCronJobResult()),
            $this->repository->notification(),
            $this->repository->routine(),
            $this->repository->whitelist(),
            $this->logger
        );
    }

    /**
     * @return ilSrCleanUpCronJob
     */
    public function cleanUp() : ilSrCleanUpCronJob
    {
        return new ilSrCleanUpCronJob(
            new ResultBuilder(new ilCronJobResult()),
            $this->logger
        );
    }
}