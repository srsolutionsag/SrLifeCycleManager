<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrDryRoutineCronJob extends ilSrAbstractCronJob
{
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routine Job (Dry)';
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
        $this->result_builder->request();

        // do magic in here

        return $this->result_builder
            ->success()
            ->message($this->translator->txt('all_good'))
            ->getResult()
            ;
    }
}