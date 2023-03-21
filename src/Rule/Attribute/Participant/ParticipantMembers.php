<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant;

use ilCourseParticipants;

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
