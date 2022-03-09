<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement\Group;

use LogicException;
use ilDBInterface;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupRequirement implements IGroupRequirement
{
    /**
     * @var ilDBInterface|null
     */
    protected $database;

    /**
     * @var ilObjGroup|null
     */
    protected $group;

    /**
     * @param ilDBInterface|null $database
     * @param ilObjGroup|null    $group
     */
    public function __construct(
        ?ilDBInterface $database = null,
        ?ilObjGroup $group = null
    ) {
        $this->database = $database;
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function getDatabase() : ilDBInterface
    {
        if (null === $this->database) {
            throw new LogicException("Dependency " . ilDBInterface::class . " was required but never injected.");
        }

        return $this->database;
    }

    /**
     * @inheritDoc
     */
    public function getGroup() : ilObjGroup
    {
        if (null === $this->group) {
            throw new LogicException("Dependency " . ilObjGroup::class . " was required but never injected.");
        }

        return $this->group;
    }
}