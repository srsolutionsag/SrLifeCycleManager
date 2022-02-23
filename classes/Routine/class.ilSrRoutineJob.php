<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineJob extends ilSrAbstractCronJob
{
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routines';
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
    public function run()
    {
        // TODO: Implement run() method.
    }
}