<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron\Routine;

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\IDeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObject;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\IDeletableObject;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\WhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use DateTimeImmutable;
use ArrayIterator;
use DateInterval;
use ilObjCourse;
use ilObject;
use ilLogger;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineCronJobTest extends TestCase
{
    /**
     * @var IRoutine
     */
    protected $pseudo_routine;

    /**
     * @var ResultBuilder
     */
    protected $result_builder;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $this->result_builder = $this->createMock(ResultBuilder::class);
        $this->result_builder->method('request')->willReturn($this->result_builder);
        $this->pseudo_routine = $this->createMock(IRoutine::class);
        $this->pseudo_routine->method('getRoutineId')->willReturn(1);
        $this->notification_repository = $this->createMock(INotificationRepository::class);
        $this->routine_repository = $this->createMock(IRoutineRepository::class);
        $this->whitelist_repository = $this->createMock(IWhitelistRepository::class);
        $this->logger = $this->createMock(ilLogger::class);
    }

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
                        1,
                        false,
                        new DateTimeImmutable(),
                        10
                    );
                }

                return null;
            }
        );

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            $this->whitelist_repository,
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
                        (new DateTimeImmutable())->sub(new DateInterval("P11D")),
                        10
                    );
                }

                return null;
            }
        );

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            $this->whitelist_repository,
            [
                $this->getCourseMock($whitelisted_ref_id),
                $this->getCourseMock($deletable_ref_id),
            ]
        );

        $job->run();

        $this->assertCount(2, $job->getDeletedObjects());
        $this->assertEquals(
            $whitelisted_ref_id,
            $job->getDeletedObjects()[0]->getRefId()
        );
        $this->assertEquals(
            $deletable_ref_id,
            $job->getDeletedObjects()[1]->getRefId()
        );
    }

    public function testDeletedObjectsWithOptOutAndActiveElongation() : void
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
                        true,
                        new DateTimeImmutable(),
                        10
                    );
                }

                return null;
            }
        );

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            $this->whitelist_repository,
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

    public function testNotificationSendingBehaviour() : void
    {
        $test_ref_id = 1;
        $starting_date = DateTimeImmutable::createFromFormat('Y-m-d', "2022-01-01");
        $initial_notification = new Notification(
            $this->pseudo_routine->getRoutineId(),
            "test 1",
            "content 1",
            10,
            1,
            $test_ref_id
        );
        $second_notification = new Notification(
            $this->pseudo_routine->getRoutineId(),
            "test 2",
            "content 2",
            5,
            2,
            $test_ref_id
        );
        $third_notification = new Notification(
            $this->pseudo_routine->getRoutineId(),
            "test 3",
            "content 3",
            1,
            3,
            $test_ref_id
        );

        $this->whitelist_repository->method('get')->willReturn(null);
        // IMPORTANT: must be ordered by days_before_submission ASC
        $this->notification_repository->method('getByRoutine')->willReturn([
            $initial_notification,
            $second_notification,
            $third_notification,
        ]);

        $sent_notifications = [];
        $this->notification_repository->method('getSentNotifications')->willReturnCallback(
            static function() use (&$sent_notifications) : array {
                return $sent_notifications;
            }
        );

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            $this->whitelist_repository,
            [
                $this->getCourseMock($test_ref_id),
            ]
        );

        $job->setDate($starting_date);
        $job->run();

        // test if the initial notification has been sent.
        $this->assertCount(1, $job->getNotifiedObjects());
        $this->assertCount(0, $job->getDeletedObjects());
        $this->assertEquals(
            $test_ref_id,
            $job->getNotifiedObjects()[0][ilObject::class]->getRefId()
        );
        $this->assertEquals(
            $initial_notification->getNotificationId(),
            $job->getNotifiedObjects()[0][INotification::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
        ];

        $job->setDate($starting_date);
        $job->run();

        // test that the same notification will not be sent again and
        // that the object is not deleted.
        $this->assertCount(0, $job->getNotifiedObjects());
        $this->assertCount(0, $job->getDeletedObjects());

        $date_of_second_notification = $starting_date->add(new DateInterval("P5D"));
        $job->setDate($date_of_second_notification);
        $job->run();

        // test if the second notification will be sent (five days later).
        $this->assertCount(1, $job->getNotifiedObjects());
        $this->assertCount(0, $job->getDeletedObjects());
        $this->assertEquals(
            $test_ref_id,
            $job->getNotifiedObjects()[0][ilObject::class]->getRefId()
        );
        $this->assertEquals(
            $second_notification->getNotificationId(),
            $job->getNotifiedObjects()[0][INotification::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
            $second_notification->setNotifiedDate($date_of_second_notification),
        ];

        $date_of_third_notification = $date_of_second_notification->add(new DateInterval("P4D"));
        $job->setDate($date_of_third_notification);
        $job->run();

        // test if the third notification will be sent (nine days later).
        $this->assertCount(1, $job->getNotifiedObjects());
        $this->assertCount(0, $job->getDeletedObjects());
        $this->assertEquals(
            $test_ref_id,
            $job->getNotifiedObjects()[0][ilObject::class]->getRefId()
        );
        $this->assertEquals(
            $third_notification->getNotificationId(),
            $job->getNotifiedObjects()[0][INotification::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
            $second_notification->setNotifiedDate($date_of_second_notification),
            $third_notification->setNotifiedDate($date_of_third_notification)
        ];

        $date_of_deletion = $date_of_third_notification->add(new DateInterval("P1D"));
        $job->setDate($date_of_deletion);
        $job->run();

        // test that the object will be deleted one day after the third
        // notifications has been sent.
        $this->assertCount(0, $job->getNotifiedObjects());
        $this->assertCount(1, $job->getDeletedObjects());
        $this->assertEquals(
            $test_ref_id,
            $job->getDeletedObjects()[0]->getRefId()
        );
    }

    /**
     * @param ilObject[] $objects
     * @return IDeletableObjectProvider
     */
    protected function getGeneratorMock(array $objects) : IDeletableObjectProvider
    {
        return new class($this->pseudo_routine, $objects) extends ArrayIterator implements IDeletableObjectProvider {
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
     * @param INotificationRepository $notifications
     * @param IRoutineRepository      $routines
     * @param IWhitelistRepository    $whitelist
     * @param ilObject[]              $objects
     * @return RoutineCronJobTestObject
     */
    protected function getTestableRoutineJob(
        INotificationRepository $notifications,
        IRoutineRepository $routines,
        IWhitelistRepository $whitelist,
        array $objects
    ) : RoutineCronJobTestObject {
        return new RoutineCronJobTestObject(
            $this->createMock(INotificationSender::class),
            $this->getGeneratorMock($objects),
            $this->result_builder,
            $notifications,
            $routines,
            $whitelist,
            $this->createMock(ilLogger::class)
        );
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
            static function () use ($ref_id) : int {
                return $ref_id;
            }
        );

        return $obj;
    }
}