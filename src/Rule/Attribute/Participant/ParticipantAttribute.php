<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ParticipantAttribute implements IAttribute
{
    /**
     * @var ilParticipants
     */
    private $participants;

    public function __construct(ilParticipants $participants)
    {
        $this->participants = $participants;
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

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->getConsideredMembers($participants);

            case self::COMPARABLE_VALUE_TYPE_INT:
                return count($this->getConsideredMembers($participants));

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->getConsideredMembers($participants));

            default:
                return null;
        }
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
