<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron;

use PHPUnit\Framework\TestSuite;
use srag\Plugins\SrLifeCycleManager\Tests\Cron\Routine\RoutineCronJobTest;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CronTestSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        $suite->addTestSuite(RoutineCronJobTest::class);

        return $suite;
    }
}