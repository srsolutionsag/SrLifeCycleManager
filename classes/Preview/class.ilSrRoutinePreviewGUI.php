<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\ComparisonFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\RessourceFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey\SurveyAttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttributeFactory;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewGUI extends ilSrAbstractGUI
{
    // ilSrRoutinePreviewGUI command/method names:;
    protected const CMD_RENDER_ASYNC = 'renderAsync';

    // ilSrRoutinePreviewGUI language variables:
    protected const PAGE_TITLE = 'page_title_routine_preview';

    /**
     * @var ilSrRoutinePreviewRenderer
     */
    protected $preview_renderer;

    /**
     * Initializes the object provider for the current preview.
     */
    public function __construct()
    {
        parent::__construct();

        $this->preview_renderer = new ilSrRoutinePreviewRenderer(
            ilSrLifeCycleManagerPlugin::getInstance()->getContainer()->getDeletableObjectProvider(),
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
