<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;

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
        return '...';
    }

    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        parent::execute();

        foreach ($this->deleted_objects as $object) {
            $this->info("Object {$object->getRefId()} ({$object->getType()}) would have been deleted.");
        }

        foreach ($this->notified_objects as $notified_object) {
            $this->info("Object {$notified_object[1]->getRefId()} ({$notified_object[1]->getType()}) would have been notified 
            with notification {$notified_object[0]->getNotificationId()} ({$notified_object[0]->getTitle()})");
        }
    }

    /**
     * @inheritDoc
     */
    protected function deleteObject(ilObject $object) : void
    {
        $this->deletable_objects[] = $object;
    }

    /**
     * @inheritDoc
     */
    protected function notifyObject(INotification $notification, ilObject $object) : void
    {
        $this->notified_objects[] = [
            $notification,
            $object,
        ];
    }
}