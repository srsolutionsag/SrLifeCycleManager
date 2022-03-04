<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Config implements IConfig
{
    /**
     * @var int[]
     */
    protected $privileged_roles = [];

    /**
     * @var bool
     */
    protected $can_tool_show = false;

    /**
     * @var bool
     */
    protected $can_tool_create = false;

    /**
     * @param int[] $privileged_roles
     * @param bool  $can_tool_show
     * @param bool  $can_tool_create
     */
    public function __construct(
        array $privileged_roles = [],
        bool $can_tool_show = false,
        bool $can_tool_create = false
    ) {
        $this->privileged_roles = $privileged_roles;
        $this->can_tool_show = $can_tool_show;
        $this->can_tool_create = $can_tool_create;
    }

    /**
     * @return array
     */
    public function getPrivilegedRoles() : array
    {
        return $this->privileged_roles;
    }

    /**
     * @inheritDoc
     */
    public function setPrivilegedRoles(array $privileged_roles) : IConfig
    {
        $this->privileged_roles = $privileged_roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function showRoutinesInRepository() : bool
    {
        return $this->can_tool_show;
    }

    /**
     * @inheritDoc
     */
    public function setShowRoutinesInRepository(bool $can_show) : IConfig
    {
        $this->can_tool_show = $can_show;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createRoutinesInRepository() : bool
    {
        return $this->can_tool_create;
    }

    /**
     * @inheritDoc
     */
    public function setCreateRoutinesInRepository(bool $can_create) : IConfig
    {
        $this->can_tool_create = $can_create;
        return $this;
    }
}