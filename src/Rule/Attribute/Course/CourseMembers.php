<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use ilCourseParticipants;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseMembers extends CourseParticipantAttribute
{
    /**
     * @inheritDoc
     */
    protected function getConsideredMembers(\ilParticipants $participants): array
    {
        return $participants->getMembers();
    }
}
