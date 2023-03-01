<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\ParticipantAttribute;
use ilCourseParticipants;
use ilParticipants;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class CourseParticipantAttribute extends ParticipantAttribute
{
    /**
     * @var ilCourseParticipants|null
     */
    private $participants;

    public function __construct(ilObjCourse $course)
    {
        $this->participants = $course->getMemberObject();
    }

    /**
     * @inheritDoc
     */
    protected function getParticipants(): ?ilParticipants
    {
        return $this->participants;
    }
}
