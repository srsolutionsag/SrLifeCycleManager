<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseParticipants extends CourseParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(ilParticipants $participants): array
    {
        return $participants->getParticipants();
    }
}