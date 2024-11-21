<?php /*********************************************************************
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
     * @var int|null
     */
    protected $routine_id;

    /**
     * @var int|null
     */
    protected $ref_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var bool
     */
    protected $is_active;

    /**
     * @var bool
     */
    protected $is_recursive;

    /**
     * @param int      $user_id
     * @param int|null $routine
     * @param int|null $ref_id
     * @param bool     $is_active
     * @param bool     $is_recursive
     */
    public function __construct(
        int $user_id,
        int $routine = null,
        int $ref_id = null,
        bool $is_active = false,
        bool $is_recursive = false
    ) {
        $this->user_id = $user_id;
        $this->routine_id = $routine;
        $this->is_active = $is_active;
        $this->is_recursive = $is_recursive;
        $this->ref_id = $ref_id;
    }

    /**
     * @inheritdoc
     */
    public function getRoutineId() : ?int
    {
        return $this->routine_id;
    }

    /**
     * @inheritdoc
     */
    public function setRoutineId(?int $routine_id) : IRoutineAssignment
    {
        $this->routine_id = $routine_id;
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
    public function setRefId(?int $ref_id) : IRoutineAssignment
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * @inheritDoc
     */
    public function setUserId(int $user_id) : IRoutineAssignment
    {
        $this->user_id = $user_id;
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
}