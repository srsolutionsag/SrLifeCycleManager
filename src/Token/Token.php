<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Token;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Token implements IToken
{
    /**
     * @param int $routine_id
     * @param int $ref_id
     * @param string $event
     * @param string $token
     */
    public function __construct(
        protected int $routine_id,
        protected int $ref_id,
        protected string $event,
        protected string $token
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId(): int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id): IToken
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(int $ref_id): IToken
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function setEvent(string $event): IToken
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function setToken(string $token): IToken
    {
        $this->token = $token;
        return $this;
    }
}
