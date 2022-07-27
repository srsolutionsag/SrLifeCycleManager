<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron\Routine;

require_once __DIR__ . '/../../../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\Reminder;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObject;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\IObserver;
use PHPUnit\Framework\TestCase;
use ilObjCourse;
use ilObject;
use ilLogger;
use DateTimeImmutable;
use DateInterval;
use Generator;

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
     * @var IReminderRepository
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
        $this->result_builder = $this->createMock(ResultBuilder::class);
        $this->result_builder->method('request')->willReturn($this->result_builder);
        $this->pseudo_routine = $this->createMock(IRoutine::class);
        $this->pseudo_routine->method('getRoutineId')->willReturn(1);
        $this->notification_repository = $this->createMock(IReminderRepository::class);
        $this->routine_repository = $this->createMock(IRoutineRepository::class);
        $this->logger = $this->createMock(ilLogger::class);
    }

    public function testNotificationSendingBehaviour() : void
    {
        $test_ref_id = 1;
        $starting_date = DateTimeImmutable::createFromFormat('Y-m-d', "2022-01-01");
        $initial_notification = new Reminder(
            $this->pseudo_routine->getRoutineId(),
            "test 1",
            "content 1",
            10,
            1,
            $test_ref_id
        );
        $second_notification = new Reminder(
            $this->pseudo_routine->getRoutineId(),
            "test 2",
            "content 2",
            5,
            2,
            $test_ref_id
        );
        $third_notification = new Reminder(
            $this->pseudo_routine->getRoutineId(),
            "test 3",
            "content 3",
            1,
            3,
            $test_ref_id
        );

        // IMPORTANT: must be ordered by days_before_submission ASC
        $this->notification_repository->method('getByRoutine')->willReturn([
            $initial_notification,
            $second_notification,
            $third_notification,
        ]);

        $sent_notifications = [];
        $this->notification_repository->method('getSentByRoutineAndObject')->willReturnCallback(
            static function () use (&$sent_notifications) : array {
                return $sent_notifications;
            }
        );

        $test_objects = [
            $this->getCourseMock($test_ref_id),
        ];

        $job = $this->getTestableRoutineJob(
            $this->notification_repository,
            $this->routine_repository,
            $test_objects
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
            $job->getNotifiedObjects()[0][IReminder::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
        ];

        $job->rewind($this->getGeneratorMock($test_objects));
        $job->setDate($starting_date);
        $job->run();

        // test that the same notification will not be sent again and
        // that the object is not deleted.
        $this->assertCount(0, $job->getNotifiedObjects());
        $this->assertCount(0, $job->getDeletedObjects());

        $date_of_second_notification = $starting_date->add(new DateInterval("P5D"));
        $job->rewind($this->getGeneratorMock($test_objects));
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
            $job->getNotifiedObjects()[0][IReminder::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
            $second_notification->setNotifiedDate($date_of_second_notification),
        ];

        $date_of_third_notification = $date_of_second_notification->add(new DateInterval("P4D"));
        $job->rewind($this->getGeneratorMock($test_objects));
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
            $job->getNotifiedObjects()[0][IReminder::class]->getNotificationId()
        );

        $sent_notifications = [
            $initial_notification->setNotifiedDate($starting_date),
            $second_notification->setNotifiedDate($date_of_second_notification),
            $third_notification->setNotifiedDate($date_of_third_notification)
        ];

        $date_of_deletion = $date_of_third_notification->add(new DateInterval("P1D"));
        $job->rewind($this->getGeneratorMock($test_objects));
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
     * @return DeletableObjectProvider
     */
    protected function getGeneratorMock(array $objects) : DeletableObjectProvider
    {
        return new class($objects, $this->pseudo_routine) extends DeletableObjectProvider {
            /**
             * @var ilObject[]
             */
            protected $objects;

            /**
             * @var IRoutine
             */
            protected $pseudo_routine;

            /**
             * @param ilObject[] $objects
             */
            public function __construct(array $objects, IRoutine $routine)
            {
                $this->objects = $objects;
                $this->pseudo_routine = $routine;
            }

            /**
             * @inheritDoc
             */
            public function getDeletableObjects() : Generator
            {
                foreach ($this->objects as $object) {
                    yield new DeletableObject(
                        $object,
                        [$this->pseudo_routine]
                    );
                }
            }
        };
    }

    /**
     * @param IReminderRepository $notifications
     * @param IRoutineRepository  $routines
     * @param ilObject[]          $objects
     * @return TestableRoutineCronJob
     */
    protected function getTestableRoutineJob(
        IReminderRepository $notifications,
        IRoutineRepository $routines,
        array $objects
    ) : TestableRoutineCronJob {
        return new TestableRoutineCronJob(
            $this->createMock(INotificationSender::class),
            $this->getGeneratorMock($objects),
            $this->result_builder,
            $this->createMock(IObserver::class),
            $notifications,
            $routines,
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