<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Assignment;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignment implements IRoutineAssignment
{
    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var bool
     */
    protected $is_active;

    /**
     * @var bool
     */
    protected $is_recursive;

    /**
     * @var int|null
     */
    protected $ref_id;

    /**
     * @param IRoutine $routine
     * @param bool     $is_active
     * @param bool     $is_recursive
     * @param int|null $ref_id
     */
    public function __construct(
        IRoutine $routine,
        bool $is_active,
        bool $is_recursive,
        int $ref_id = null
    ) {
        $this->routine = $routine;
        $this->is_active = $is_active;
        $this->is_recursive = $is_recursive;
        $this->ref_id = $ref_id;
    }

    /**
     * @inheritdoc
     */
    public function getRoutine() : IRoutine
    {
        return $this->routine;
    }

    /**
     * @inheritdoc
     */
    public function setRoutine(IRoutine $routine) : IRoutineAssignment
    {
        $this->routine = $routine;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isActive() : bool
    {
        return $this->is_active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $is_active) : IRoutineAssignment
    {
        $this->is_active = $is_active;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isRecursive() : bool
    {
        return $this->is_recursive;
    }

    /**
     * @inheritDoc
     */
    public function setRecursive(bool $is_recursive) : IRoutineAssignment
    {
        $this->is_recursive = $is_recursive;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId() : ?int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(int $ref_id) : IRoutineAssignment
    {
        $this->ref_id = $ref_id;
        return $this;
    }
}