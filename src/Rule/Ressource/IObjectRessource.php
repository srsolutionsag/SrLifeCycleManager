<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IObjectRessource extends IDatabaseRessource
{
    /**
     * Provides dynamic attributes with an ILIAS repository object.
     */
    public function getObject(): \ilObject;
}
