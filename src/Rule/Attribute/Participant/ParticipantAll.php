<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant;

use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ParticipantAll extends ParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(ilParticipants $participants): array
    {
        return $participants->getParticipants();
    }
}