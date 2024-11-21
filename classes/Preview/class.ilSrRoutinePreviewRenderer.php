<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewRenderer extends ilSrAbstractRoutinePreviewGenerator
{
    // ilSrRoutinePreviewAsyncGUI paths:
    protected const PREVIEW_STYLESHEET = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/css/routine_preview.css';
    protected const PREVIEW_TEMPLATE = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/tpl.routine_preview.html';
    protected const PREVIEW_SCRIPT = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/js/preview_loader.js';

    // ilSrRoutinePreviewAsyncGUI language variables:
    protected const PREVIEW_REF_ID = 'preview_ref_id';
    protected const PREVIEW_ROUTINES = 'preview_routines';
    protected const PREVIEW_OBJECT_LINK = 'preview_link_goto_object';
    protected const LABEL_DELETED_OBJECTS = 'label_preview_deleted_objects';
    protected const MSG_LOADING = 'msg_this_may_take_a_while';

    protected AffectedObjectProvider $affected_object_provider;

    /**
     * @param mixed $ui_factory
     */
    public function __construct(
        AffectedObjectProvider $affected_object_provider,
        protected ITranslator $translator,
        protected Renderer $renderer,
        protected UIFactory $ui_factory,
        protected string $ajax_action
    ) {
        parent::__construct($affected_object_provider);
        $this->affected_object_provider = $affected_object_provider;
    }

    public function registerResources(ilGlobalTemplateInterface $template): void
    {
        $template->addJavaScript(self::PREVIEW_SCRIPT);
        $template->addCss(self::PREVIEW_STYLESHEET);
    }

    public function getLoader(): Component
    {
        $template = new ilTemplate(self::PREVIEW_TEMPLATE, true, true);

        $template->setVariable('MESSAGE', $this->translator->txt(self::MSG_LOADING));
        $template->setVariable('ASYNC_URL', $this->ajax_action);

        try {
            $html = $template->get();
        } catch (ilTemplateException) {
            $html = '';
        }

        return $this->ui_factory->legacy($html);
    }

    public function getPreview(): string
    {
        $items = [];
        foreach ($this->affected_object_provider->getAffectedObjects() as $item) {
            $instance = $item->getObject();
            $properties = [
                $this->translator->txt(self::PREVIEW_REF_ID) => $instance->getRefId()
            ];

            if (!isset($properties[$this->translator->txt(self::PREVIEW_ROUTINES)])) {
                $properties[$this->translator->txt(self::PREVIEW_ROUTINES)] = '';
            }

            $properties[$this->translator->txt(self::PREVIEW_ROUTINES)] .= $item->getRoutine()->getTitle() . '<br>';

            $links = $this->ui_factory->dropdown()->standard([
                $this->ui_factory->link()->standard(
                    $this->translator->txt(self::PREVIEW_OBJECT_LINK),
                    ilLink::_getStaticLink($instance->getRefId())
                )->withOpenInNewViewport(true),
            ]);

            $items[] = $this->ui_factory
                ->item()
                ->standard($instance->getTitle())
                ->withProperties(
                    $properties
                )->withActions($links);
        }

        $item_group = $this->ui_factory->item()->group(
            $this->translator->txt(self::LABEL_DELETED_OBJECTS),
            $items
        );

        return $this->renderer->renderAsync([
            $this->ui_factory->messageBox()->info(count($items) . ' Item(s)'),
            $item_group
        ]);
    }
}
