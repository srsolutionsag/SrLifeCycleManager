<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron;

use PHPUnit\Framework\TestCase;
use ilSrRoutineCronJob;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineCronJobTest extends TestCase
{
    /**
     * @var ilSrRoutineCronJob
     */
    protected $cron_job;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {

    }

    public function testDeletedObjectsWithWhitelistEntry() : void
    {

    }

    public function testDeletedObjectsWithoutWhitelistEntry() : void
    {

    }

    public function testDeletedObjectsWithElapsedWhitelistEntry() : void
    {

    }

    public function testNotificationSendingBehaviour() : void
    {

    }
}