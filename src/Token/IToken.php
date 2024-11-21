<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Token;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IToken
{
    // IToken attributes:
    public const F_ROUTINE_ID = 'routine_id';
    public const F_REF_ID = 'ref_id';
    public const F_TOKEN = 'token';
    public const F_EVENT = 'event';

    /**
     * @return int
     */
    public function getRoutineId(): int;

    /**
     * @param int $routine_id
     * @return IToken
     */
    public function setRoutineId(int $routine_id): IToken;

    /**
     * @return int
     */
    public function getRefId(): int;

    /**
     * @param int $ref_id
     * @return IToken
     */
    public function setRefId(int $ref_id): IToken;

    /**
     * @return string
     */
    public function getEvent(): string;

    /**
     * @param string $event
     * @return IToken
     */
    public function setEvent(string $event): IToken;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param string $token
     * @return IToken
     */
    public function setToken(string $token): IToken;
}
