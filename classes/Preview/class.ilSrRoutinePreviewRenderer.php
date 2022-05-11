<?php declare(strict_types=1);

/* Copyright (c) 2022 Fabian Schmid <fabian@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\Provider\ObjectProvider;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewRenderer
{
    // ilSrRoutinePreviewAsyncGUI paths:
    protected const PREVIEW_STYLESHEET = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/css/routine_preview.css';
    protected const PREVIEW_TEMPLATE = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/tpl.routine_preview.html';
    protected const PREVIEW_SCRIPT = 'Customizing/global/plugins/Services/Cron/CronHook/SrLifeCycleManager/templates/default/js/preview_loader.js';

    // ilSrRoutinePreviewAsyncGUI language variables:
    protected const PREVIEW_REF_ID = 'preview_ref_id';
    protected const PREVIEW_ROUTINES = 'preview_routines';
    protected const PREVIEW_OBJECT_LINK = 'preview_link_goto_object';
    protected const MSG_LOADING = 'msg_this_may_take_a_while';

    /**
     * @var ObjectProvider
     */
    protected $object_provider;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var UIFactory
     */
    protected $ui_factory;

    /**
     * @var string
     */
    protected $ajax_action;

    /**
     * @param ObjectProvider $object_provider
     * @param ITranslator    $translator
     * @param Renderer       $renderer
     * @param UIFactory      $ui_factory
     * @param string         $ajax_action
     */
    public function __construct(
        ObjectProvider $object_provider,
        ITranslator $translator,
        Renderer $renderer,
        UIFactory $ui_factory,
        string $ajax_action
    ) {
        $this->translator = $translator;
        $this->object_provider = $object_provider;
        $this->renderer = $renderer;
        $this->ui_factory = $ui_factory;
        $this->ajax_action = $ajax_action;
    }

    /**
     * @param ilGlobalTemplateInterface $template
     * @return void
     */
    public function registerResources(ilGlobalTemplateInterface $template) : void
    {
        $template->addJavaScript(self::PREVIEW_SCRIPT);
        $template->addCss(self::PREVIEW_STYLESHEET);
    }

    /**
     * @return Component
     */
    public function getLoader() : Component
    {
        $template = new ilTemplate(self::PREVIEW_TEMPLATE, true, true);

        $template->setVariable('MESSAGE', $this->translator->txt(self::MSG_LOADING));
        $template->setVariable('ASYNC_URL', $this->ajax_action);

        try {
            $html = $template->get();
        } catch (ilTemplateException $e) {
            $html = '';
        }

        return $this->ui_factory->legacy($html);
    }

    /**
     * @return string
     */
    public function getPreview() : string
    {
        $items = [];
        foreach ($this->object_provider->getDeletableObjects() as $item) {
            $instance = $item->getInstance();
            $properties = [
                $this->translator->txt(self::PREVIEW_REF_ID) => $instance->getRefId()
            ];

            foreach ($item->getAffectingRoutines() as $routine) {
                $properties[$this->translator->txt(self::PREVIEW_ROUTINES)] .= $routine->getTitle() . '<br>';
            }

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
                )->withActions($links)
            ;
        }

        return $this->renderer->renderAsync(
            array_merge([$this->ui_factory->messageBox()->info(count($items) . ' Item(s)')], $items)
        );
    }
}
