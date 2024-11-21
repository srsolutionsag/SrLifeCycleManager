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

use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IParticipantRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common\CommonNull;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ParticipantAttributeFactory implements IDynamicAttributeProvider
{
    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return ParticipantAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [
            ParticipantAdmins::class,
            ParticipantTutors::class,
            ParticipantMembers::class,
            ParticipantAll::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(IRessource $ressource, string $value): IAttribute
    {
        if (!$ressource instanceof IParticipantRessource) {
            return new CommonNull();
        }

        switch ($value) {
            case ParticipantAdmins::class:
                return new ParticipantAdmins($ressource->getParticipants());
            case ParticipantTutors::class:
                return new ParticipantTutors($ressource->getParticipants());
            case ParticipantMembers::class:
                return new ParticipantMembers($ressource->getParticipants());
            case ParticipantAll::class:
                return new ParticipantAll($ressource->getParticipants());

            default:
                return new CommonNull();
        }
    }
}
