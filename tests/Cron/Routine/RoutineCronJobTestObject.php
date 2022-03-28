<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Tests\Cron\Routine;

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use ilSrRoutineCronJob;
use DateTimeImmutable;
use LogicException;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineCronJobTestObject extends ilSrRoutineCronJob
{
    /**
     * @var ilObject[]
     */
    protected $deleted_objects = [];

    /**
     * @var array<int, array<ilObject|INotification>>
     */
    protected $notified_objects = [];

    /**
     * @var DateTimeImmutable|null
     */
    protected $current_date;

    /**
     * @ineritdoc
     */
    protected function execute() : void
    {
        $this->deleted_objects = [];
        $this->notified_objects = [];

        parent::execute();
    }

    /**
     * @return ilObject[]
     */
    public function getDeletedObjects() : array
    {
        return $this->deleted_objects;
    }

    /**
     * @return array<int, array<ilObject|INotification>>
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
        $this->notified_objects[] = [
            INotification::class => $notification,
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