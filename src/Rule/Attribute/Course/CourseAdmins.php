<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use ilParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseAdmins extends CourseParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(ilParticipants $participants): array
    {
        return $participants->getAdmins();
    }
}
