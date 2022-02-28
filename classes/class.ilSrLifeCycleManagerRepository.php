<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\IRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerRepository implements IRepository
{


    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @param ilDBInterface $database
     * @param ilTree        $tree
     */
    public function __construct(ilDBInterface $database, ilTree $tree)
    {
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function config() : IConfigRepository
    {
        // TODO: Implement config() method.
    }

    /**
     * @inheritDoc
     */
    public function routine() : IRoutineRepository
    {
        // TODO: Implement routine() method.
    }

    /**
     * @inheritDoc
     */
    public function notification() : INotificationRepository
    {
        // TODO: Implement notification() method.
    }

    /**
     * @inheritDoc
     */
    public function rule() : IRuleRepository
    {
        // TODO: Implement rule() method.
    }
}