<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Whitelist;

use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WhitelistEntry implements IWhitelistEntry
{
    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var bool
     */
    protected $is_opt_out;

    /**
     * @var DateTimeImmutable|null
     */
    protected $expiry_date;

    /**
     * @var DateTimeImmutable
     */
    protected $date;

    /**
     * @param int                    $routine_id
     * @param int                    $ref_id
     * @param int                    $user_id
     * @param bool                   $is_opt_out
     * @param DateTimeImmutable      $date
     * @param DateTimeImmutable|null $expiry_date
     */
    public function __construct(
        int $routine_id,
        int $ref_id,
        int $user_id,
        bool $is_opt_out,
        DateTimeImmutable $date,
        DateTimeImmutable $expiry_date = null
    ) {
        $this->routine_id = $routine_id;
        $this->ref_id = $ref_id;
        $this->user_id = $user_id;
        $this->is_opt_out = $is_opt_out;
        $this->expiry_date = $expiry_date;
        $this->date = $date;
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
    public function getDate(): DateTimeImmutable
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

    /**
     * @inheritDoc
     */
    public function isExpired($when): bool
    {
        if ($this->is_opt_out) {
            return false;
        }

        if (null === $this->expiry_date) {
            return true;
        }

        return ($when > $this->expiry_date);
    }
}