<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\IRepository;
use ILIAS\DI\RBACServices;

/**
 * This repository serves as a factory for all repositories.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The repository itself should not implement any operations,
 * everything should be done in one of the implementations.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerRepository implements IRepository
{
    use ilSrRepositoryHelper;

    /**
     * @var IConfigRepository
     */
    protected $config_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @param ilDBInterface $database
     * @param RBACServices  $rbac
     * @param ilTree        $tree
     */
    public function __construct(ilDBInterface $database, RBACServices $rbac, ilTree $tree)
    {
        $this->config_repository       = new ilSrConfigRepository($database, $rbac);
        $this->notification_repository = new ilSrNotificationRepository($database);
        $this->routine_repository      = new ilSrRoutineRepository($database, $tree);
        $this->whitelist_repository    = new ilSrWhitelistRepository($database);
        $this->rule_repository         = new ilSrRuleRepository($database, $tree);

        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function config() : IConfigRepository
    {
        return $this->config_repository;
    }

    /**
     * @inheritDoc
     */
    public function routine() : IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @inheritdoc
     */
    public function whitelist() : IWhitelistRepository
    {
        return $this->whitelist_repository;
    }

    /**
     * @inheritDoc
     */
    public function notification() : INotificationRepository
    {
        return $this->notification_repository;
    }

    /**
     * @inheritDoc
     */
    public function rule() : IRuleRepository
    {
        return $this->rule_repository;
    }
}