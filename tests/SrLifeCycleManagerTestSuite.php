<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests;

require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestSuite;
use srag\Plugins\SrLifeCycleManager\Tests\Cron\RoutineCronJobTest;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SrLifeCycleManagerTestSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/Cron/RoutineCronJobTest.php';
        $suite->addTestSuite(RoutineCronJobTest::class);

        return $suite;
    }
}