<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
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
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var IConfigRepository
     */
    protected $config_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @param ilDBInterface $database
     * @param RBACServices  $rbac
     * @param ilTree        $tree
     */
    public function __construct(ilDBInterface $database, RBACServices $rbac, ilTree $tree)
    {
        $this->tree = $tree;
        $this->config_repository = new ilSrConfigRepository($database, $rbac);
        $this->notification_repository = new ilSrNotificationRepository($database);
        $this->rule_repository = new ilSrRuleRepository($database, $tree);
        $this->routine_repository = new ilSrRoutineRepository(
            new ilSrWhitelistRepository($database),
            $database,
            $tree
        );
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

    /**
     * @inheritDoc
     */
    public function getRepositoryObjects(int $ref_id) : Generator
    {
        $container_objects = $this->tree->getChildsByTypeFilter($ref_id, ['crs', 'cat', 'grp', 'fold']);
        if (empty($container_objects)) {
            yield new EmptyIterator();
        }

        foreach ($container_objects as $container) {
            if (in_array($container['type'], IRoutine::ROUTINE_TYPES, true)) {
                yield ilObjectFactory::getInstanceByRefId((int) $container['ref_id']);
            } else {
                yield from $this->getRepositoryObjects((int) $container['ref_id']);
            }
        }
    }
}