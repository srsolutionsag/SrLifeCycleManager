<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron\Routine;

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ilSrRoutineCronJob;
use ilObject;
use DateTimeImmutable;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class TestableRoutineCronJob extends ilSrRoutineCronJob
{
    /**
     * @var ilObject[]
     */
    protected $deleted_objects = [];

    /**
     * @var array<int, array<ilObject|IReminder>>
     */
    protected $notified_objects = [];

    /**
     * @var DateTimeImmutable|null
     */
    protected $current_date;

    /**
     * @return ilObject[]
     */
    public function getDeletedObjects() : array
    {
        return $this->deleted_objects;
    }

    /**
     * @return array<int, array<ilObject|IReminder>>
     */
    public function getNotifiedObjects() : array
    {
        return $this->notified_objects;
    }

    /**
     * @param DateTimeImmutable $date
     */
    public function setDate(DateTimeImmutable $date) : void
    {
        $this->current_date = $date;
    }

    /**
     * required during tests with the same instance to avoid an
     * "already traversed generator" exception.
     *
     * @param DeletableObjectProvider $new_provider
     */
    public function rewind(DeletableObjectProvider $new_provider) : void
    {
        $this->object_provider = $new_provider;
        $this->notified_objects = [];
        $this->deleted_objects = [];
    }

    /**
     * @inheritDoc
     */
    protected function deleteObject(IRoutine $routine, ilObject $object) : void
    {
        $this->deleted_objects[] = $object;
    }

    /**
     * @inheritDoc
     */
    protected function notifyObject(IReminder $notification, ilObject $object) : void
    {
        $this->notified_objects[] = [
            IReminder::class => $notification,
            ilObject::class => $object,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getDate() : DateTimeImmutable
    {
        if (null === $this->current_date) {
            throw new LogicException("Must set current date with RoutineCronJobTestObject::setDate().");
        }

        return $this->current_date;
    }
}