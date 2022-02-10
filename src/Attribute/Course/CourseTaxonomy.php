<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilDBInterface;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseTaxonomy extends CourseAttribute
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var array
     */
    protected $taxonomies;

    /**
     * @param ilDBInterface $database
     * @param ilObjCourse    $course
     */
    public function __construct(ilDBInterface $database, ilObjCourse $course)
    {
        parent::__construct($course);

        $this->database = $database;
        $this->taxonomies = $this->getTaxonomies();
    }

    /**
     * @inheritDoc
     */
    public function getComparableTypes() : array
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
        if (self::COMPARABLE_VALUE_TYPE_ARRAY === $type) {
            return $this->taxonomies;
        }

        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return implode(',', $this->taxonomies);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getTaxonomies() : array
    {
        return $this->database->fetchAll(
            $this->database->queryF(
                "
                    SELECT tn.title AS title FROM tax_node_assignment AS ta
                        JOIN tax_node AS tn ON tn.obj_id = ta.node_id
                        WHERE ta.obj_id = %s;

                ",
                ['integer'],
                [$this->course->getId()]
            )
        );
    }
}