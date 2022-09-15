<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Token;

use Throwable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait TokenGenerator
{
    /**
     * Returns a 64 character long and cryptographically secure token.
     *
     * @return string
     */
    protected function generateToken() : string
    {
        // random_bytes() is cryptographically secure but
        // depends on the system it's running on. If the
        // generation fails, we use a less secure option
        // that is available for sure.

        try {
            $token = bin2hex(random_bytes(64));
        } catch (Throwable $t) {
            $token = hash('sha256', uniqid((string) time(), true));
        }

        return $token;
    }
}