<?php

declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObject;

/**
 * This cron-job "simulates" what would happen if the actual routine
 * cron-job was executed.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrDryRoutineCronJob extends ilSrRoutineCronJob
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return 'SrLifeCycleManager Dry Routine Job';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Emulates the routine-cron-job and gathers its information.';
    }

    /**
     * @inheritDoc
     */
    protected function deleteObject(AffectedObject $affected_object): void
    {
        $routine = $affected_object->getRoutine();
        $object = $affected_object->getObject();

        $this->addSummary(
            sprintf(
                'Object "%s" (%d) would have been deleted by routine "%s" (%d).',
                $object->getTitle(),
                $object->getRefId(),
                $routine->getTitle(),
                $routine->getRoutineId()
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function notifyObject(IReminder $notification, AffectedObject $affected_object): void
    {
        $routine = $affected_object->getRoutine();
        $object = $affected_object->getObject();

        $this->addSummary(
            sprintf(
                'Reminder "%s" (%d) from routine "%s" (%d) would have been sent to object "%s" (%d).',
                $notification->getTitle(),
                $notification->getNotificationId(),
                $routine->getTitle(),
                $routine->getRoutineId(),
                $object->getTitle(),
                $object->getRefId()
            )
        );
    }
}
