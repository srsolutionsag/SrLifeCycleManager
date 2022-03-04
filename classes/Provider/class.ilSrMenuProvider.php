<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;

/**
 * This class provides ILIAS with menu entries.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The provider is currently unused, though it might be possible in
 * the future to create a link to the plugin-configuration or the
 * routine GUI index.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrMenuProvider extends AbstractStaticMainMenuPluginProvider
{
    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        return [];
    }
}