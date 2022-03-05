<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * This builder is responsible for managing the cron-job results
 * of this plugin.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The usage of this builder might not be that straight forward,
 * because the result object must be requested BEFORE the actual
 * cron job has started.
 *
 * This has been done so the builder is responsible to keep track
 * of the elapsed time and the cron jobs need only be updating the
 * statuses or messages before returning this result.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrResultBuilder
{
    /**
     * @var ilCronJobResult|null
     */
    protected $result;

    /**
     * @var float
     */
    protected $starting_time;

    /**
     * Initializes the result object and stores the current microtime.
     *
     * @return self
     */
    public function request() : self
    {
        $this->result = new ilCronJobResult();
        $this->starting_time = microtime(true);

        return $this;
    }

    /**
     * Sets the result objects message to the given one.
     *
     * @param string $message
     * @return self
     */
    public function message(string $message) : self
    {
        $this->result->setMessage($message);
        return $this;
    }

    /**
     * Marks the current result object as successful.
     *
     * @return self
     */
    public function success() : self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_OK);
        return $this;
    }

    /**
     * Marks the current result object as failed.
     *
     * @return self
     */
    public function failure() : self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_FAIL);
        return $this;
    }

    /**
     * Marks the current result object as crashed.
     *
     * @return self
     */
    public function crash() : self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_CRASHED);
        return $this;
    }

    /**
     * Returns the current result object with approximate duration.
     *
     * @return ilCronJobResult
     */
    public function getResult() : ilCronJobResult
    {
        if (null === $this->result) {
            throw new LogicException("Cannot get object that hasn't been initialized.");
        }

        $this->result->setDuration(
            microtime(true) - $this->starting_time
        );

        return $this->result;
    }
}