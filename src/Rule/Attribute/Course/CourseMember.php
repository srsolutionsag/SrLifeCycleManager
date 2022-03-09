<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use ilCourseParticipants;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseMember extends CourseAttribute
{
    /**
     * @var ilCourseParticipants|null
     */
    protected $course_member;

    /**
     * @param ilObjCourse $course
     */
    public function __construct(ilObjCourse $course)
    {
        $this->course_member = $course->getMembersObject();

        parent::__construct($course);
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
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
        if (null === $this->course_member) {
            return null;
        }

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->course_member->getMembers();

            case self::COMPARABLE_VALUE_TYPE_INT:
                return $this->course_member->getCountMembers();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->course_member->getMembers());

            default:
                return null;
        }
    }
}