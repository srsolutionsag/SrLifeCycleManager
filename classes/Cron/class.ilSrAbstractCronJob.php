<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Cron\Schedule\CronJobScheduleType;
use srag\Plugins\SrLifeCycleManager\Cron\ResultBuilder;
use srag\Plugins\SrLifeCycleManager\Cron\INotifier;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractCronJob extends ilCronJob
{
    protected array $summary = [];

    public function __construct(
        protected ResultBuilder $result_builder,
        protected INotifier $notifier,
        protected ?\ilGlobalTemplateInterface $template = null
    ) {
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
        if ($this->template !== null) {
            $this->template->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
                $this->getSummary('<br />'),
                true
            );
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
    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue(): int
    {
        return 1;
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
     * This method MUST implement the actual cron-job.
     *
     * The execution has been wrapped by a catch clause to manage
     * possible crashes.
     */
    abstract protected function execute(): void;
}
