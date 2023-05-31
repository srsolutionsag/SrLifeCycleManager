<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;
use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractCronJob extends ilCronJob
{
    /**
     * @var ResultBuilder
     */
    protected $result_builder;

    /**
     * @var INotifier
     */
    protected $notifier;

    /**
     * @var string[]
     */
    protected $summary = [];

    public function __construct(ResultBuilder $builder, INotifier $notifier)
    {
        $this->result_builder = $builder;
        $this->notifier = $notifier;
    }

    /**
     * @inheritDoc
     */
    public function run(): ilCronJobResult
    {
        $this->result_builder->request()->trackTime();

        try {
            $this->execute();
        } catch (Throwable $throwable) {
            return $this->result_builder
                ->crash()
                ->message($throwable->getMessage() . $throwable->getTraceAsString())
                ->getResult();
        }

        $result = $this->result_builder
            ->success()
            ->message($this->getSummary())
            ->getResult();

        // displays an info-toast with the summary of the current cron-job
        // at the top of the cron-job administration page.
        if (!$this->isCLI()) {
            ilUtil::sendInfo($this->getSummary('<br />'), true);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return static::class;
    }

    /**
     * @inheritDoc
     */
    public function hasAutoActivation(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * Returns the summary glued together (each entry as a new line).
     */
    protected function getSummary(string $line_break = PHP_EOL): string
    {
        $message = 'Successfully terminated.';
        if (!empty($this->summary)) {
            $message .=
                $line_break .
                $line_break .
                implode($line_break, $this->summary);
        }

        return $message;
    }

    protected function addSummary(string $message): void
    {
        $this->summary[] = $message;
    }

    /**
     * Returns whether the cron-job instance has been started via CLI.
     */
    protected function isCLI(): bool
    {
        // cannot use PHP_SAPI in this scenario.
        return ('cli' === php_sapi_name());
    }

    /**
     * This method MUST implement the actual cron-job.
     *
     * The execution has been wrapped by a catch clause to manage
     * possible crashes.
     */
    abstract protected function execute(): void;
}
