<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Tests\Routine;

require_once __DIR__ . '/../../vendor/autoload.php';

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObject;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateInterval;
use Generator;
use ilSrRoutineCronJob;
use ilCronJobResult;
use ilObject;
use ilLogger;
use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineCronJobTest extends TestCase
{
    /**
     * @var MockObject|INotificationSender
     */
    protected $notification_sender;

    /**
     * @var MockObject|IRecipientRetriever
     */
    protected $recipient_retriever;

    /**
     * @var MockObject|IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @var MockObject|ITokenRepository
     */
    protected $token_repository;

    /**
     * @var MockObject|IReminderRepository
     */
    protected $reminder_repository;

    /**
     * @var MockObject|IGeneralRepository
     */
    protected $general_repository;

    /**
     * @var MockObject|DeletableObject
     */
    protected $deletable_object;

    /**
     * @var MockObject|DeletableObjectProvider
     */
    protected $object_provider;

    /**
     * @var MockObject|IRoutine
     */
    protected $routine;

    /**
     * @var MockObject|Observer
     */
    protected $observer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->notification_sender = $this->createMock(INotificationSender::class);
        $this->recipient_retriever = $this->createMock(IRecipientRetriever::class);
        $this->whitelist_repository = $this->createMock(IWhitelistRepository::class);
        $this->token_repository = $this->createMock(ITokenRepository::class);
        $this->reminder_repository = $this->createMock(IReminderRepository::class);
        $this->general_repository = $this->createMock(IGeneralRepository::class);

        $this->observer = $this->createMock(Observer::class);
        $this->observer->method('broadcast')->willReturnSelf();

        $this->routine = $this->createMock(IRoutine::class);
        $this->routine->method('getRoutineId')->willReturn(1);
    }

    /**
     * The cron job should always send reminders in chronological order of
     * their amount of days before deletion. Things get a little tricky when
     * objects are postponed (are whitelisted) because the notification-date
     * must then be calculated differently. This test therefor expects the
     * following chronological order:
     *
     *      01. reminder_1: 01.01.2000
     *      02. reminder_2: 21.01.2000
     *      03. reminder_3: 30.01.2000 -> postponed for 40 days
     *      04. reminder_1: 10.02.2000 -> new notification is added, elongation changed
     *      05. reminder_4: 25.02.2000
     *
     * Whereas the reminders are:
     *
     *      - reminder_1: 30 days before deletion
     *      - reminder_2: 10 days before deletion
     *      - reminder_3: 01 days before deletion
     *      - reminder_4: 15 days before deletion
     *
     * Marked by "->" are actions that could occur during several runs of
     * the cron job which must all be taken into account when sending reminders.
     */
    public function testBehaviourOfSentReminders(): void
    {
        // update this variable instead of re-configuring the whitelist
        // repository method.
        $whitelist_entry = null;

        $this->whitelist_repository->method('get')->willReturnCallback(
            static function () use (&$whitelist_entry) {
                return $whitelist_entry;
            }
        );

        $test_object = $this->getObjectMock(1);

        $reminder_1 = $this->getReminderMock(1, 30);
        $reminder_2 = $this->getReminderMock(2, 10);
        $reminder_3 = $this->getReminderMock(3, 1);

        // update these two variables instead of re-configuring the
        // reminder repository's methods.
        $with_days_before_deletion_reminder = null;
        $next_reminder = $reminder_1;
        $sent_reminder = null;
        $all_reminders = [
            $reminder_1,
            $reminder_2,
            $reminder_3,
        ];

        $this->reminder_repository->method('getLastByRoutine')->willReturn($reminder_3);
        $this->reminder_repository->method('getNextReminder')->willReturnCallback(
            static function () use (&$next_reminder) {
                return $next_reminder;
            }
        );
        $this->reminder_repository->method('getRecentlySent')->willReturnCallback(
            static function () use (&$sent_reminder) {
                return $sent_reminder;
            }
        );
        $this->reminder_repository->method('getByRoutine')->willReturnCallback(
            static function () use (&$all_reminders) {
                return $all_reminders;
            }
        );
        $this->reminder_repository->method('getWithDaysBeforeDeletion')->willReturnCallback(
            static function () use (&$with_days_before_deletion_reminder) {
                return $with_days_before_deletion_reminder;
            }
        );

        // assertion-logic will be handled in this callback.
        $this->notification_sender->method('sendNotification')->willReturnCallback(
            function ($retriever, $reminder, $object) use (&$next_reminder, &$test_object) {
                $this->assertInstanceOf(IReminder::class, $reminder);
                $this->assertInstanceOf(ilObject::class, $object);
                $this->assertEquals($next_reminder->getNotificationId(), $reminder->getNotificationId());
                $this->assertEquals($test_object->getRefId(), $object->getRefId());

                return $this->createMock(ISentNotification::class);
            }
        );

        $provider = $this->getDeletableObjectsProvider([$test_object]);

        // date on which reminder_1 should be sent.
        $date_01_01_2000 = DateTimeImmutable::createFromFormat('m.d.Y', '01.01.2000');

        $cron_job = $this->getCronJob($date_01_01_2000, $provider);
        $cron_job->run();

        // date on which reminder_2 should be sent.
        $date_21_01_2000 = $date_01_01_2000->add(new DateInterval('P20D'));

        $reminder_1->method('getNotifiedDate')->willReturn($date_01_01_2000);

        $sent_reminder = $reminder_1;
        $next_reminder = $reminder_2;

        $provider->rewind();
        $cron_job = $this->getCronJob($date_21_01_2000, $provider);
        $cron_job->run();

        // date on which reminder_3 should be sent.
        $date_30_01_2000 = $date_21_01_2000->add(new DateInterval('P9D'));

        $reminder_2->method('getNotifiedDate')->willReturn($date_21_01_2000);

        $sent_reminder = $reminder_2;
        $next_reminder = $reminder_3;

        $provider->rewind();
        $cron_job = $this->getCronJob($date_30_01_2000, $provider);
        $cron_job->run();

        // date of whitelist expiry date (for 40 days elongation)
        $date_11_03_2000 = $date_30_01_2000->add(new DateInterval('P40D'));
        // date on which reminder_1 should be sent again.
        $date_10_02_2000 = $date_11_03_2000->sub(new DateInterval('P30D'));

        $reminder_3->method('getNotifiedDate')->willReturn($date_30_01_2000);

        $whitelist_entry = $this->createMock(IWhitelistEntry::class);
        $whitelist_entry->method('getExpiryDate')->willReturn($date_11_03_2000);

        $with_days_before_deletion_reminder = $reminder_1;
        $sent_reminder = $reminder_3;
        $next_reminder = $reminder_1;

        $provider->rewind();
        $cron_job = $this->getCronJob($date_10_02_2000, $provider);
        $cron_job->run();

        // emulate a new reminder being added.
        $reminder_4 = $this->getReminderMock(4, 15);
        $all_reminders = [
            $reminder_1,
            $reminder_4,
            $reminder_2,
            $reminder_3,
        ];

        // date on which reminder_4 should be sent.
        $date_25_02_2000 = $date_11_03_2000->sub(new DateInterval('P15D'));

        $with_days_before_deletion_reminder = $reminder_4;
        $sent_reminder = $reminder_2;
        $next_reminder = $reminder_4;

        $provider->rewind();
        $cron_job = $this->getCronJob($date_25_02_2000, $provider);
        $cron_job->run();
    }

    /**
     * The cron job must take the configured amount of days a reminder is sent before
     * the deletion into account when calculation the actual deletion-date. Therefore,
     * if we have one reminder:
     *
     *      - reminder_1: 30 days before deletion
     *
     * The cron job should send this reminder on the initial run and only delete the
     * object if 30 days have passed, even though the cron-job runs daily.
     *
     * This test will not cover actions that could occurr during this process (like
     * postponements or opt-outs).
     */
    public function testBehaviourOfDeletions(): void
    {
        $this->whitelist_repository->method('get')->willReturn(null);

        $test_object = $this->getObjectMock(1);
        $reminder_1 = $this->getReminderMock(1, 30);
        $date_when_reminder_1_should_be_sent = DateTimeImmutable::createFromFormat('m.d.Y', '01.01.2000');
        $date_when_object_should_be_deleted = $date_when_reminder_1_should_be_sent->add(new DateInterval("P31D"));
        $date_when_object_should_not_be_deleted = $date_when_reminder_1_should_be_sent->add(new DateInterval("P10D"));

        // please test these variables instead of the according repository-method-calls.
        $has_object_been_deleted = false;
        $has_reminder_been_sent = false;

        // please update these variables instead of re-configuring the repository mock.
        $previous_reminder = null;
        $next_reminder = $reminder_1;

        $this->reminder_repository->method('getByRoutine')->willReturn([$reminder_1]);
        $this->reminder_repository->method('getLastByRoutine')->willReturn($reminder_1);
        $this->reminder_repository->method('getRecentlySent')->willReturnCallback(
            static function () use (&$previous_reminder) {
                return $previous_reminder;
            }
        );
        $this->reminder_repository->method('getNextReminder')->willReturnCallback(
            static function () use (&$next_reminder) {
                return $next_reminder;
            }
        );

        $this->notification_sender->method('sendNotification')->willReturnCallback(
            function () use (&$has_reminder_been_sent) {
                $has_reminder_been_sent = true;
                return $this->createMock(ISentNotification::class);
            }
        );

        $this->general_repository->method('deleteObject')->willReturnCallback(
            function () use (&$has_object_been_deleted) {
                $has_object_been_deleted = true;
                return true;
            }
        );

        $provider = $this->getDeletableObjectsProvider([$test_object]);
        $cron_job = $this->getCronJob($date_when_reminder_1_should_be_sent, $provider);
        $cron_job->run();

        $this->assertTrue($has_reminder_been_sent);
        $this->assertFalse($has_object_been_deleted);

        // update the reminder variables to emulate that reminder_1 has been sent on the correct date.
        $reminder_1->method('getNotifiedDate')->willReturn($date_when_reminder_1_should_be_sent);
        $reminder_1->method('isElapsed')->willReturnCallback(
        // emulates the same logic as to the original method.
            static function ($when) use ($reminder_1, $date_when_reminder_1_should_be_sent) {
                $elapsed_date = $date_when_reminder_1_should_be_sent->add(
                    new DateInterval("P{$reminder_1->getDaysBeforeDeletion()}D")
                );

                return ($when > $elapsed_date);
            }
        );
        $previous_reminder = $reminder_1;
        $next_reminder = null;

        $provider->rewind();
        $cron_job = $this->getCronJob($date_when_object_should_not_be_deleted, $provider);
        $cron_job->run();

        $this->assertFalse($has_object_been_deleted);

        $provider->rewind();
        $cron_job = $this->getCronJob($date_when_object_should_be_deleted, $provider);
        $cron_job->run();

        $this->assertTrue($has_object_been_deleted);
    }

    /**
     * The cron job should be able to delete multiple objects that were
     * affected by a routine. This test checks if more than one object
     * is deleted for the three following scenarios:
     *
     *      (a) there is a whitelist entry that has expired.
     *      (b) there is no whitelist entry and no reminder.
     *      (c) there is no whitelist entry all reminders have been sent.
     */
    public function testDeletionOfObjects(): void
    {
        $object_1 = $this->getObjectMock(1);
        $object_2 = $this->getObjectMock(2);

        // expect a total of 6 calls, two deletions for each scenario.
        $this->general_repository
            ->expects(self::exactly(6))
            ->method('deleteObject')
            ->withConsecutive(
                [$object_1->getRefId()],
                [$object_2->getRefId()],
                [$object_1->getRefId()],
                [$object_2->getRefId()],
                [$object_1->getRefId()],
                [$object_2->getRefId()]
            );

        // test scenario (a):

        $whitelist_entry = $this->createMock(IWhitelistEntry::class);
        $whitelist_entry->method('isExpired')->willReturn(true);

        $this->whitelist_repository->method('get')->willReturn($whitelist_entry);

        $provider = $this->getDeletableObjectsProvider([$object_1, $object_2]);
        $cron_job = $this->getCronJob(new DateTimeImmutable(), $provider);
        $cron_job->run();

        // test scenario (b):

        $all_reminders = [];
        $this->reminder_repository->method('getByRoutine')->willReturnCallback(
            static function () use (&$all_reminders) {
                return $all_reminders;
            }
        );

        $provider->rewind();
        $cron_job = $this->getCronJob(new DateTimeImmutable(), $provider);
        $cron_job->run();

        // test scenario (c):

        $last_reminder = $this->getReminderMock(1, 1);

        $this->reminder_repository->method('getLastByRoutine')->willReturn($last_reminder);
        $this->reminder_repository->method('getRecentlySent')->willReturn($last_reminder);

        $provider->rewind();
        $cron_job = $this->getCronJob(new DateTimeImmutable(), $provider);
        $cron_job->run();
    }

    /**
     * The cron job should manually remove all associated internal data of a
     * deleted object (because foreign-keys are not yet supported by ILIAS).
     */
    public function testRemovalOfInternalData(): void
    {
        $object = $this->getObjectMock(1);

        $whitelist_entry = $this->createMock(IWhitelistEntry::class);
        $whitelist_entry->method('isExpired')->willReturn(true);

        $this->whitelist_repository->method('get')->willReturn($whitelist_entry);

        $this->general_repository
            ->expects(self::once())
            ->method('deleteObject')
            ->with($object->getRefId());

        $this->reminder_repository
            ->expects(self::once())
            ->method('markObjectAsDeleted')
            ->with($object->getRefId());

        $this->token_repository
            ->expects(self::once())
            ->method('delete')
            ->with($object->getRefId());

        $cron_job = $this->getCronJob(
            new DateTimeImmutable(),
            $this->getDeletableObjectsProvider([$object])
        );

        $cron_job->run();
    }

    /**
     * The cron job should broadcast an according routine event if an object
     * is deleted.
     */
    public function testBroadcastOfEvents(): void
    {
        $object = $this->getObjectMock(1);

        $whitelist_entry = $this->createMock(IWhitelistEntry::class);
        $whitelist_entry->method('isExpired')->willReturn(true);

        $this->whitelist_repository->method('get')->willReturn($whitelist_entry);

        $this->observer->method('broadcast')->willReturnCallback(
            function ($event) use ($object) {
                $this->assertInstanceOf(RoutineEvent::class, $event);
                $this->assertEquals(RoutineEvent::EVENT_DELETE, $event->getName());
                $this->assertEquals($this->routine->getRoutineId(), $event->getRoutine()->getRoutineId());
                $this->assertEquals($object->getRefId(), $event->getObject()->getRefId());
            }
        );

        $cron_job = $this->getCronJob(
            new DateTimeImmutable(),
            $this->getDeletableObjectsProvider([$object])
        );

        $cron_job->run();
    }

    /**
     * @param DateTimeImmutable       $execution_date
     * @param DeletableObjectProvider $provider
     * @return ilSrRoutineCronJob
     */
    protected function getCronJob(
        DateTimeImmutable $execution_date,
        DeletableObjectProvider $provider
    ): ilSrRoutineCronJob {
        return new class (
            $this->notification_sender,
            $this->recipient_retriever,
            $provider,
            $this->createMock(ResultBuilder::class),
            $this->observer,
            $this->reminder_repository,
            $this->token_repository,
            $this->whitelist_repository,
            $this->general_repository,
            $this->createMock(ilLogger::class),
            $execution_date,
            $this->createMock(ilCronJobResult::class)
        ) extends ilSrRoutineCronJob {
            private $current_date;
            private $result;

            public function __construct(
                INotificationSender $notification_sender,
                IRecipientRetriever $recipient_retriever,
                DeletableObjectProvider $object_provider,
                ResultBuilder $result_builder,
                Observer $event_observer,
                IReminderRepository $notification_repository,
                ITokenRepository $token_repository,
                IWhitelistRepository $whitelist_repository,
                IGeneralRepository $general_repository,
                ilLogger $logger,
                DateTimeImmutable $execution_date,
                ilCronJobResult $result
            ) {
                parent::__construct(
                    $notification_sender,
                    $recipient_retriever,
                    $object_provider,
                    $result_builder,
                    $event_observer,
                    $notification_repository,
                    $token_repository,
                    $whitelist_repository,
                    $general_repository,
                    $logger
                );
                $this->current_date = $execution_date;
                $this->result = $result;
            }

            /**
             * Since the cron-job is built to be fail-safe we need to override the
             * run function where all the code is run within a try and catch.
             *
             * @inheritDoc
             */
            public function run(): ilCronJobResult
            {
                $this->execute();
                return $this->result;
            }

            /**
             * Since the cron-job must be run on different dates we have to override
             * the function used to get the current date.
             *
             * @inheritDoc
             */
            protected function getCurrentDate(): DateTimeImmutable
            {
                return $this->current_date;
            }
        };
    }

    /**
     * @param ilObject[] $objects
     * @return DeletableObjectProvider
     */
    protected function getDeletableObjectsProvider(array $objects): DeletableObjectProvider
    {
        $deletable_objects = [];

        foreach ($objects as $object) {
            $deletable_object = $this->createMock(DeletableObject::class);
            $deletable_object->method('getInstance')->willReturn($object);
            $deletable_object->method('getAffectingRoutines')->willReturn([
                $this->routine,
            ]);

            $deletable_objects[] = $deletable_object;
        }

        return new class ($deletable_objects) extends DeletableObjectProvider {
            /**
             * @param DeletableObject[] $objects
             */
            private $objects;

            /**
             * @param DeletableObject[] $objects
             * @noinspection PhpMissingParentConstructorInspection
             */
            public function __construct(array $objects)
            {
                $this->objects = $objects;
            }

            /**
             * @inheritDoc
             */
            public function getDeletableObjects(): Generator
            {
                yield from $this->objects;
            }

            /**
             * In order to keep the same instance of this provider this
             * method can be used to rewind the generator returned by
             * the overwritten function.
             */
            public function rewind(): void
            {
                reset($this->objects);
            }
        };
    }

    /**
     * @param int $reminder_id
     * @param int $days_before_deletion
     * @return MockObject|IReminder
     */
    protected function getReminderMock(int $reminder_id, int $days_before_deletion): IReminder
    {
        $reminder = $this->createMock(IReminder::class);
        $reminder->method('getDaysBeforeDeletion')->willReturn($days_before_deletion);
        $reminder->method('getNotificationId')->willReturn($reminder_id);

        return $reminder;
    }

    /**
     * @param int $ref_id
     * @return MockObject|ilObject
     */
    protected function getObjectMock(int $ref_id): ilObject
    {
        $object = $this->createMock(ilObject::class);
        $object->method('getRefId')->willReturn($ref_id);

        return $object;
    }
}
