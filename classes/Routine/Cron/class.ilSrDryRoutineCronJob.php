<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This cron-job "simulates" what would happen if the actual routine
 * cron-job was executed.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrDryRoutineCronJob extends ilSrRoutineCronJob
{
    /**
     * @var ilObject[]
     */
    protected $deleted_objects = [];

    /**
     * @var array<int, array>
     */
    protected $notified_objects = [];

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return 'SrLifeCycleManager Dry Routine Job';
    }

    /**
     * @inheritDoc
     */
    public function getDescription() : string
    {
        return 'Emulates the routine-cron-job and gathers its information.';
    }

    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        parent::execute();

        foreach ($this->deleted_objects as $object) {
            $message = "Object {$object->getRefId()} ({$object->getType()}) would have been deleted.";
            $this->addSummary($message);
            $this->info($message);
        }

        foreach ($this->notified_objects as $notified_object) {
            $message = "Object {$notified_object[1]->getRefId()} ({$notified_object[1]->getType()}) would have been notified
            with notification {$notified_object[0]->getNotificationId()} ({$notified_object[0]->getTitle()})";
            $this->addSummary($message);
            $this->info($message);
        }
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
            $notification,
            $object,
        ];
    }
}
