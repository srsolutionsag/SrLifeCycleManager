<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Whitelist;

use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WhitelistEntry implements IWhitelistEntry
{
    /**
     * @param int                    $routine_id
     * @param int                    $ref_id
     * @param int                    $user_id
     * @param bool                   $is_opt_out
     * @param DateTimeImmutable|null $date
     * @param DateTimeImmutable|null $expiry_date
     */
    public function __construct(
        protected int $routine_id,
        protected int $ref_id,
        protected int $user_id,
        protected bool $is_opt_out,
        protected ?\DateTimeImmutable $date = null,
        protected ?\DateTimeImmutable $expiry_date = null
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getRoutineId(): int
    {
        return $this->routine_id;
    }

    /**
     * @inheritdoc
     */
    public function setRoutineId(int $routine_id): IWhitelistEntry
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * @inheritdoc
     */
    public function setRefId(int $ref_id): IWhitelistEntry
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @inheritdoc
     */
    public function setUserId(int $user_id): IWhitelistEntry
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isOptOut(): bool
    {
        return $this->is_opt_out;
    }

    /**
     * @inheritdoc
     */
    public function setOptOut(bool $is_opt_out): IWhitelistEntry
    {
        $this->is_opt_out = $is_opt_out;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDate(): ?DateTimeImmutable
    {
        return $this->expiry_date;
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDate(DateTimeImmutable $date): IWhitelistEntry
    {
        $this->expiry_date = $date;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(DateTimeImmutable $date): IWhitelistEntry
    {
        $this->date = $date;
        return $this;
    }
}
