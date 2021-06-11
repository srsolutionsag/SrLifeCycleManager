<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

/**
 * Class ilSrLifeCycleManagerToolsProvider provides ILIAS with this plugin's tools.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * The provider is currently only interessted in the repository context, as we want
 * to allow our tools to appear just within course objects, if configured.
 */
class ilSrLifeCycleManagerToolsProvider extends AbstractDynamicToolPluginProvider
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
        return [];
    }
}