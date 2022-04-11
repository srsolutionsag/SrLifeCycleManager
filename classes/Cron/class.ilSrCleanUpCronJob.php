<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrCleanUpCronJob extends ilSrAbstractCronJob
{
    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        // @todo: clean-up whitelist entries and notified-objects here.
    }
}