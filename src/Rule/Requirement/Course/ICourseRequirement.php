<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IDatabaseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\IRequirement;

use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ICourseRequirement extends IRequirement, IDatabaseRequirement
{
    /**
     * @return ilObjCourse
     */
    public function getCourse() : ilObjCourse;
}