<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationJob extends ilSrAbstractCronJob
{
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Notifications';
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '...';
    }

    /**
     * @inheritDoc
     */
    public function run() : ilCronJobResult
    {
        foreach ($this->repository->routine()->getAll() as $routine) {

        }
    }

    /**
     * @return bool
     */
    protected function isNotificationDate() : bool
    {

    }
}