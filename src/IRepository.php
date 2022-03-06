<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager;

use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
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
     * @param int $ref_id
     * @return Generator|ilObject[]
     */
    public function getRepositoryObjectGenerator(int $ref_id) : Generator;
}