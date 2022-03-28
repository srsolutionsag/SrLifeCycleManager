<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Rule;

use PHPUnit\Framework\TestSuite;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleTestSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        return $suite;
    }
}