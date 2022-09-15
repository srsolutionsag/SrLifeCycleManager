<?php

namespace srag\Plugins\SrLifeCycleManager\Token;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ITokenRepository
{
    /**
     * @param int    $routine_id
     * @param int    $ref_id
     * @param string $event
     * @return IToken|null
     */
    public function get(int $routine_id, int $ref_id, string $event): ?IToken;

    /**
     * @param string $token
     * @return IToken|null
     */
    public function getByToken(string $token): ?IToken;

    /**
     * @param int    $routine_id
     * @param int    $ref_id
     * @param string $event
     * @return IToken
     */
    public function new(int $routine_id, int $ref_id, string $event): IToken;

    /**
     * @param IToken $token
     * @return bool
     */
    public function redeem(IToken $token): bool;

    /**
     * @param int $ref_id
     * @return bool
     */
    public function delete(int $ref_id): bool;
}
