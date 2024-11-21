<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Cron;

use ilCronJobResult;
use LogicException;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ResultBuilder implements IResultBuilder
{
    /**
     * corresponds to the max length of ilCronJobResult::setMessage().
     */
    protected const MAX_MESSAGE_LENGTH = 400;

    /**
     * @var ilCronJobResult
     */
    protected $boilerplate;

    /**
     * @var ilCronJobResult|null
     */
    protected $result;

    /**
     * @var float|null
     */
    protected $start;

    /**
     * @param ilCronJobResult $boilerplate
     */
    public function __construct(ilCronJobResult $boilerplate)
    {
        $this->boilerplate = $boilerplate;
    }

    /**
     * @inheritDoc
     */
    public function request(): IResultBuilder
    {
        $this->result = $this->boilerplate;
        $this->start = null;

        return $this;
    }

    /**
     * Tells the builder to track the elapsed time until the result is
     * returned. Therefore, this method must be called BEFORE the actual
     * cron-job starts his operations for accuracy.
     *
     * @return self
     */
    public function trackTime(): self
    {
        $this->start = microtime(true);
        return $this;
    }

    /**
     * Sets the current results message to the one given.
     *
     * @param string $message
     * @param string[] $summary
     * @return self
     */
    public function message(string $message, array $summary = []): self
    {
        $message_combined = $message . (
            $summary !== []
                ? PHP_EOL . PHP_EOL . implode(PHP_EOL, $summary)
                : ''
        );
        $this->result->setMessage($this->cropMessage($message_combined));
        return $this;
    }

    /**
     * Marks the current result as successful.
     *
     * @return self
     */
    public function success(): self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_OK);
        return $this;
    }

    /**
     * Marks the current result as failed.
     *
     * @return self
     */
    public function failure(): self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_FAIL);
        return $this;
    }

    /**
     * Marks the current result as crashed.
     *
     * @return self
     */
    public function crash(): self
    {
        $this->result->setStatus(ilCronJobResult::STATUS_CRASHED);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResult(): ilCronJobResult
    {
        if (null === $this->result) {
            throw new LogicException("Cannot build object without requesting it.");
        }

        if (null !== $this->start) {
            $this->result->setDuration(
                microtime(true) - $this->start
            );
        }

        return $this->result;
    }

    protected function cropMessage(string $message, int $length = self::MAX_MESSAGE_LENGTH): string
    {
        return substr($message, 0, ($length - 3)) . '...';
    }
}
