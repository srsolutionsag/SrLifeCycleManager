<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager;

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use Generator;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRepository
{
    /**
     * @return IConfigRepository
     */
    public function config() : IConfigRepository;

    /**
     * @return IRoutineRepository
     */
    public function routine() : IRoutineRepository;

    /**
     * @return IWhitelistRepository
     */
    public function whitelist() : IWhitelistRepository;

    /**
     * @return INotificationRepository
     */
    public function notification() : INotificationRepository;

    /**
     * @return IRuleRepository
     */
    public function rule() : IRuleRepository;

    /**
     * Returns all repository objects that can be deleted by a routine
     * starting from the given ref-id.
     *
     * @return Generator|ilObject[]
     */
    public function getRepositoryObjects() : Generator;
}