<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineWhitelistJob extends ilSrAbstractCronJob
{
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Whitelist Entries';
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
        // @TODO: remove all whitelist entries that aren't opt-outs and after today.
    }
}