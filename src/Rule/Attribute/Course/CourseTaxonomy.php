<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\TaxonomyHelper;
use ilDBInterface;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseTaxonomy extends CourseAttribute
{
    use TaxonomyHelper;

    /**
     * @var string[]
     */
    protected $taxonomies;

    /**
     * @param ilDBInterface $database
     * @param ilObjCourse    $course
     */
    public function __construct(ilDBInterface $database, ilObjCourse $course)
    {
        parent::__construct($course);

        $this->taxonomies = $this->getTaxonomies(
            $database,
            $course->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes() : array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_ARRAY,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getComparableValue(string $type)
    {
        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->taxonomies;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->taxonomies);

            default:
                return null;
        }
    }
}