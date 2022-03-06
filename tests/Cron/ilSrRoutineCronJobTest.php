<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Rule\Generator\DeletableObject;
use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObject;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Routine\WhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use ArrayIterator;
use DateTime;
use ilSrRoutineCronJob;
use ilCronJobResult;
use ilObjCourse;
use ilObject;
use ilLogger;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineCronJobTest extends TestCase
{
    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $this->routine = $this->createMock(IRoutine::class);
        $this->routine_repository = $this->createMock(IRoutineRepository::class);
        $this->whitelist_repository = $this->createMock(IWhitelistRepository::class);
        $this->notification_repository = $this->createMock(INotificationRepository::class);
        $this->logger = $this->createMock(ilLogger::class);
    }

    /**
     * description ...
     */
    public function testDeletedObjectsWithActiveElongation() : void
    {
        $whitelisted_ref_id = 1;
        $deletable_ref_id = 2;

        $this->notification_repository->method('getByRoutine')->willReturn([]);
        $this->notification_repository->method('getSentNotifications')->willReturn([]);
        $this->whitelist_repository->method('get')->willReturnCallback(
            static function ($routine, $ref_id) use ($whitelisted_ref_id) {
                // return the whitelist entry only for the whitelisted test object.
                if ($ref_id === $whitelisted_ref_id) {
                    return new WhitelistEntry(
                        1,
                        $whitelisted_ref_id,
                        false,
                        new DateTime(),
                        10
                    );
                }

                return null;
            }
        );

        $this->routine_repository->method('whitelist')->willReturn($this->whitelist_repository);

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            [
                $this->getCourseMock($whitelisted_ref_id),
                $this->getCourseMock($deletable_ref_id),
            ]
        );

        $job->run();

        $this->assertCount(1, $job->getDeletedObjects());
        $this->assertEquals(
            $deletable_ref_id,
            $job->getDeletedObjects()[0]->getRefId()
        );
    }

    public function testDeletedObjectsWithElapsedElongation() : void
    {

    }

    public function testDeletedObjectsWithOptOut() : void
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

    /**
     * @param INotificationRepository $notifications
     * @param IRoutineRepository      $routines
     * @param ilObject[]              $objects
     * @return ilSrRoutineCronJob
     */
    protected function getTestableRoutineJob(
        INotificationRepository $notifications,
        IRoutineRepository $routines,
        array $objects
    ) : ilSrRoutineCronJob {
        return new class(
            $this->createMock(INotificationSender::class),
            $this->getGeneratorMock($objects),
            new ResultBuilder($this->createMock(ilCronJobResult::class)),
            $notifications,
            $routines,
            $this->logger
        ) extends ilSrRoutineCronJob {
            /** @var array  */
            protected $deleted_objects = [];
            /** @var array  */
            protected $notified_objects = [];

            /**
             * @return array
             */
            public function getDeletedObjects() : array
            {
                return $this->deleted_objects;
            }

            /**
             * @return array
             */
            public function getNotifiedObjectsArray() : array
            {
                return $this->notified_objects;
            }

            /**
             * @inheritDoc
             */
            protected function deleteObject(ilObject $object) : void
            {
                $this->deleted_objects[] = $object;
            }

            /**
             * @inheritDoc
             */
            protected function notifyObject(INotification $notification, ilObject $object) : void
            {
                $this->notified_objects = [$notification, $object];
            }
        };
    }

    /**
     * @param ilObject[] $objects
     * @return IDeletableObjectGenerator
     */
    protected function getGeneratorMock(array $objects) : IDeletableObjectGenerator
    {
        return new class($this->routine, $objects) extends ArrayIterator implements IDeletableObjectGenerator {
            /** @var IRoutine */
            protected $routine;

            /**
             * @param IRoutine $routine
             * @param array    $objects
             */
            public function __construct(IRoutine $routine, array $objects)
            {
                parent::__construct($objects);
                $this->routine = $routine;
            }

            /**
             * @inheritdoc
             */
            public function current() : ?IDeletableObject
            {
                $current = parent::current();
                if ($current) {
                    return new DeletableObject(
                        $current,
                        [$this->routine]
                    );
                }

                return null;
            }
        };
    }

    /**
     * @param int $ref_id
     * @return ilObjCourse
     */
    protected function getCourseMock(int $ref_id) : ilObjCourse
    {
        $obj = $this->createMock(ilObjCourse::class);
        $obj->method('getType')->willReturn('crs');
        $obj->method('getRefId')->willReturnCallback(
            static function () use ($ref_id) {
                return $ref_id;
            }
        );

        return $obj;
    }
}