<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Tests\Whitelist\Event;

use srag\Plugins\SrLifeCycleManager\Whitelist\Event\WhitelistEventListener;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WhitelistEventListenerTest extends TestCase
{
    /**
     * @var MockObject|IConfirmationRepository
     */
    protected $confirmation_repository;

    /**
     * @var MockObject|ISentNotification
     */
    protected $sent_notification;

    /**
     * @var INotificationSender
     */
    protected $notification_sender;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->confirmation_repository = $this->createMock(IConfirmationRepository::class);
        $this->sent_notification = $this->createMock(ISentNotification::class);
        $this->notification_sender = $this->getNotificationSenderMock();
    }

    public function testDeliveryWithRepeatableEvent() : void
    {
        $listener = $this->getEventListener();

    }

    /**
     * @return WhitelistEventListener
     */
    public function getEventListener() : WhitelistEventListener
    {
        return new WhitelistEventListener(
            $this->confirmation_repository,
            $this->notification_sender
        );
    }

    /**
     * @return INotificationSender
     */
    protected function getNotificationSenderMock() : INotificationSender
    {
        return new class($this->sent_notification) implements INotificationSender {
            /**
             * @var ISentNotification
             */
            protected $sent_notification_mock;

            /**
             * @var bool
             */
            protected $has_been_notified;

            /**
             * @param ISentNotification $mock
             */
            public function __construct(ISentNotification $mock)
            {
                $this->sent_notification_mock = $mock;
            }

            /**
             * @inheritDoc
             */
            public function sendNotification(INotification $notification, ilObject $object) : ISentNotification
            {
                $this->has_been_notified = true;

                return $this->sent_notification_mock;
            }

            /**
             * @return bool
             */
            public function hasBeendNotified() : bool
            {
                return $this->has_been_notified;
            }
        };
    }
}
