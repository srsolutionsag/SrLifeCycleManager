<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\BackgroundTaskServices;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewGUI extends ilSrAbstractGUI
{
    // ilSrRoutinePreviewGUI command/method names:
    public const CMD_SHOW_ASYNC_PREVIEW = 'showAsyncPreview';
    public const CMD_START_BACKGROUND_TASK = 'startBackgroundTask';
    protected const CMD_RENDER_ASYNC = 'renderAsync';

    // ilSrRoutinePreviewGUI language variables:
    protected const PAGE_TITLE = 'page_title_routine_preview';

    protected BackgroundTaskServices $background_tasks;

    protected \ilSrRoutinePreviewRenderer $preview_renderer;

    /**
     * Initializes the object provider for the current preview.
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilSrLifeCycleManagerPlugin */
        $plugin = $component_factory->getPlugin(ilSrLifeCycleManagerPlugin::PLUGIN_ID);

        $this->background_tasks = $DIC->backgroundTasks();

        $this->preview_renderer = new ilSrRoutinePreviewRenderer(
            $plugin->getContainer()->getAffectedObjectProvider(),
            $this->translator,
            $this->renderer,
            $this->ui_factory,
            $this->getAjaxAction()
        );
    }

    /**
     * @inheritDoc
     */
    protected function setupGlobalTemplate(ilGlobalTemplateInterface $template, ilSrTabManager $tabs): void
    {
        $this->preview_renderer->registerResources($template);

        $template->setTitle($this->translator->txt(self::PAGE_TITLE));
        $tabs
            ->addConfigurationTab()
            ->addRoutineTab()
            ->addPreviewTab(true);
    }

    /**
     * @inheritDoc
     */
    protected function canUserExecute(ilSrAccessHandler $access_handler, string $command): bool
    {
        // only routine-managers can execute commands in this gui.
        return $this->access_handler->canManageRoutines();
    }

    /**
     * Displays a loader (moving circle) on the current page, that renders
     * list items  asynchronously.
     *
     * @see ilSrRoutinePreviewGUI::renderAsync()
     */
    protected function index(): void
    {
        $async_button_text = $this->translator->txt('async_preview');
        $bulky_async_button = $this->ui_factory->button()->bulky(
            $this->ui_factory->symbol()->icon()->standard(
                $async_button_text,
                $async_button_text
            )->withAbbreviation('A')
                             ->withSize('large'),
            $async_button_text,
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_ASYNC_PREVIEW)
        );

        $background_button_text = $this->translator->txt('bgt_preview');
        $bulky_background_button = $this->ui_factory->button()->bulky(
            $this->ui_factory->symbol()->icon()->standard(
                $background_button_text,
                $background_button_text
            )->withAbbreviation('B')
                             ->withSize('large'),
            $background_button_text,
            $this->ctrl->getLinkTarget($this, self::CMD_START_BACKGROUND_TASK)
        );

        $container = $this->ui_factory->panel()->secondary()->legacy(
            $this->translator->txt('choose_preview_mode'),
            $this->ui_factory->legacy(
                $this->translator->txt('preview_mode_description') .
                $this->renderer->render([
                    $this->ui_factory->divider()->horizontal(),
                    $bulky_async_button,
                    $this->ui_factory->divider()->horizontal(),
                    $bulky_background_button
                ])
            )
        );
        $this->render($container);
    }

    protected function startBackgroundTask(): void
    {
        // Create Bucket and assign it to current user
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user->getId());

        // Create Collect Job
        $collect_job = $this->background_tasks->taskFactory()->createTask(
            ilSrRoutinePreviewBackgroundTask::class
        );
        // Create Download Interaction, add Result of Collect Job as Parameter
        $download_interaction = $this->background_tasks->taskFactory()->createTask(
            ilSrRoutinePreviewBackgroundDownloadInteraction::class,
            [$collect_job, 'LifeCycleManager-Report.txt']
        );

        // Assign Tasks to Bucket
        $bucket->setTask($download_interaction);
        $bucket->setTitle('LifeCycleManager-Report.txt');
        $this->background_tasks->taskManager()->run($bucket);
        $this->sendSuccessMessage('background_task_started');
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function showAsyncPreview(): void
    {
        $this->render($this->preview_renderer->getLoader());
    }

    /**
     * Displays the preview items asynchronously on the current page.
     */
    protected function renderAsync(): void
    {
        echo $this->preview_renderer->getPreview();
        exit;
    }

    /**
     * @return string
     */
    protected function getAjaxAction(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_RENDER_ASYNC,
            '',
            true
        );
    }
}
