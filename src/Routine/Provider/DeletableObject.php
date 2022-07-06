<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine\Provider;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DeletableObject
{
    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @var IRoutine[]
     */
    protected $affected_routines;

    /**
     * @param ilObject   $object
     * @param IRoutine[] $affected_routines
     */
    public function __construct(ilObject $object, array $affected_routines)
    {
        $this->object = $object;
        $this->affected_routines = $affected_routines;
    }

    /**
     * @inheritDoc
     */
    public function getInstance() : ilObject
    {
        return $this->object;
    }

    /**
     * @inheritDoc
     */
    public function getAffectingRoutines() : array
    {
        return $this->affected_routines;
    }
}