<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;

/**
 * Class ilSrMenuProvider provides ILIAS with the plugins menu entries.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This provider currently provides one top-item where all sub-items of this
 * plugins should be added to. This way we keep an orderly fashion to things.
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