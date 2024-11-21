<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Assignment;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignment implements IRoutineAssignment
{
    /**
     * @param int $user_id
     * @param int|null $routine_id
     * @param int|null $ref_id
     * @param bool $is_active
     * @param bool $is_recursive
     */
    public function __construct(
        protected int $user_id,
        protected ?int $routine_id = null,
        protected ?int $ref_id = null,
        protected bool $is_active = false,
        protected bool $is_recursive = false
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getRoutineId(): ?int
    {
        return $this->routine_id;
    }

    /**
     * @inheritdoc
     */
    public function setRoutineId(?int $routine_id): IRoutineAssignment
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId(): ?int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(?int $ref_id): IRoutineAssignment
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @inheritDoc
     */
    public function setUserId(int $user_id): IRoutineAssignment
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $is_active): IRoutineAssignment
    {
        $this->is_active = $is_active;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isRecursive(): bool
    {
        return $this->is_recursive;
    }

    /**
     * @inheritDoc
     */
    public function setRecursive(bool $is_recursive): IRoutineAssignment
    {
        $this->is_recursive = $is_recursive;
        return $this;
    }
}
