<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupAdmins extends GroupParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(ilParticipants $participants): array
    {
        return $participants->getAdmins();
    }
}
