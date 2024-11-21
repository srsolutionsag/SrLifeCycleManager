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

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ParticipantAttribute implements IAttribute
{
    public function __construct(private \ilParticipants $participants)
    {
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_ARRAY,
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        if (null === ($participants = $this->getParticipants())) {
            return null;
        }

        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_ARRAY => $this->getConsideredMembers($participants),
            self::COMPARABLE_VALUE_TYPE_INT => count($this->getConsideredMembers($participants)),
            self::COMPARABLE_VALUE_TYPE_STRING => implode(',', $this->getConsideredMembers($participants)),
            default => null,
        };
    }

    protected function getParticipants(): ?ilParticipants
    {
        return $this->participants;
    }

    /**
     * Must return the members which should be considered by this attribute.
     * The members should be returned as an array of user-ids (preferably cast).
     *
     * @return int[]
     */
    abstract protected function getConsideredMembers(ilParticipants $participants): array;
}
