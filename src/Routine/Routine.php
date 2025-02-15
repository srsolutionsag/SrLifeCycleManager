<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Routine implements IRoutine
{
    /**
     * @param int               $owner_id
     * @param string            $routine_type
     * @param int               $origin
     * @param string            $title
     * @param bool              $has_opt_out
     * @param DateTimeImmutable $creation_date
     * @param int|null          $elongation
     * @param int|null          $elongation_cooldown
     * @param int|null          $routine_id
     */
    public function __construct(
        protected int $owner_id,
        protected string $routine_type,
        protected int $origin,
        protected string $title,
        protected bool $has_opt_out,
        protected \DateTimeImmutable $creation_date,
        protected ?int $elongation = null,
        protected ?int $elongation_cooldown = null,
        protected ?int $routine_id = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId(): ?int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id): IRoutine
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    /**
     * @inheritDoc
     */
    public function setOwnerId(int $owner_id): IRoutine
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrigin(): int
    {
        return $this->origin;
    }

    /**
     * @inheritDoc
     */
    public function setOrigin(int $origin): IRoutine
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineType(): string
    {
        return $this->routine_type;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineType(string $routine_type): IRoutine
    {
        $this->routine_type = $routine_type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): IRoutine
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasOptOut(): bool
    {
        return $this->has_opt_out;
    }

    /**
     * @inheritDoc
     */
    public function setOptOut(bool $has_opt_out): IRoutine
    {
        $this->has_opt_out = $has_opt_out;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getElongation(): ?int
    {
        return $this->elongation;
    }

    /**
     * @inheritDoc
     */
    public function setElongation(?int $amount): IRoutine
    {
        $this->elongation = $amount;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getElongationCooldown(): ?int
    {
        return $this->elongation_cooldown;
    }

    /**
     * @inheritDoc
     */
    public function setElongationCooldown(?int $amount_in_days): IRoutine
    {
        $this->elongation_cooldown = $amount_in_days;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    /**
     * @inheritDoc
     */
    public function setCreationDate(DateTimeImmutable $creation_date): IRoutine
    {
        $this->creation_date = $creation_date;
        return $this;
    }
}
