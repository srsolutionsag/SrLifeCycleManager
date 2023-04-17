<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IParticipantRessource extends IRessource
{
    /**
     * Provides dynamic attributes with object participants.
     */
    public function getParticipants(): \ilParticipants;
}
