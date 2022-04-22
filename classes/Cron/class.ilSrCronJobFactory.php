<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Repository\RepositoryFactory;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\ObjectProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use ILIAS\DI\RBACServices;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrCronJobFactory
{
    /**
     * @var RepositoryFactory
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
     * @var ObjectProvider
     */
    protected $object_provider;

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
        $this->repository = new RepositoryFactory(
            new ilSrGeneralRepository($database, $tree, $rbac),
            new ilSrConfigRepository($database, $rbac),
            new ilSrRoutineRepository($database, $tree),
            new ilSrAssignmentRepository($database, $tree),
            new ilSrRuleRepository($database, $tree),
            new ilSrNotificationRepository($database),
            new ilSrWhitelistRepository($database)
        );

        $this->object_provider = new ObjectProvider(
            new RoutineProvider(
                new ComparisonFactory(
                    new RequirementFactory($database),
                    new AttributeFactory()
                ),
                $this->repository->routine(),
                $this->repository->rule()
            ),
            $this->repository->general()
        );

        $this->mail_factory = $mail_factory;
        $this->database = $database;
        $this->tree = $tree;
        $this->logger = $logger;
        $this->rbac = $rbac;
        $this->ctrl = $ctrl;
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
            $this->object_provider->getDeletableObjects(),
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
            $this->object_provider->getDeletableObjects(),
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
