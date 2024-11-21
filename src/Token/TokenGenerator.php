<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

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
            // the hexadecimal representation will need 2 characters for
            // every byte, therefore we have to cut the random-bytes in
            // half.
            $token = bin2hex(random_bytes(32));
        } catch (Throwable $t) {
            $token = hash('sha256', uniqid((string) time(), true));
        }

        return $token;
    }
}