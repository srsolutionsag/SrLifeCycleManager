<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ParticipantMembers extends ParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(\ilParticipants $participants): array
    {
        return $participants->getMembers();
    }
}
