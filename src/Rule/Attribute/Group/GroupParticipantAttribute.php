<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\ParticipantAttribute;
use ilGroupParticipants;
use ilParticipants;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class GroupParticipantAttribute extends ParticipantAttribute
{
    /**
     * @var ilGroupParticipants|null
     */
    private $participants;

    public function __construct(ilObjGroup $group)
    {
        $this->participants = $group->getMembersObject();
    }

    /**
     * @inheritDoc
     */
    protected function getParticipants(): ?ilParticipants
    {
        return $this->participants;
    }
}
