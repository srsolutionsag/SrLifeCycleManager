<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDatabaseRessource extends IRessource
{
    /**
     * Provides dynamic attributes with a database instance.
     */
    public function getDatabase(): \ilDBInterface;
}
