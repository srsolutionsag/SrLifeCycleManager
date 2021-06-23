<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

/**
 * Class ilSrToolProvider provides ILIAS with the plugin's tools.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * The provider is currently only interested in the repository context, as we want
 * to allow our tools to appear just within course objects, if configured.
 */
class ilSrToolProvider extends AbstractDynamicToolPluginProvider
{
    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->repository();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        return [
            $this->factory
                ->tool($this->if->identifier($this->getPluginID() . '_tool'))
                ->withTitle($this->plugin->txt('tool_main_entry'))
                ->withContent(
                    $this->dic->ui()->factory()->legacy(
                        '<a href="' . ilSrLifeCycleManagerDispatcher::buildQualifiedLinkTarget(ilSrRoutineGUI::class, ilSrRoutineGUI::CMD_ROUTINE_INDEX) .'">link to stuff</a>'
                    )
                )
                ->withAvailableCallable(function() : bool {
                    return (bool) $this->plugin->isActive();
                })
                ->withVisibilityCallable(function() : bool {
                    return ilSrAccess::canUserDoStuff();
                })
            ,
        ];
    }
}