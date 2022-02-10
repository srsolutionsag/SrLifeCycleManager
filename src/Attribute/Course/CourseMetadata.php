<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Attribute;

use ilDBInterface;
use ilObjCourse;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CourseMetadata extends CourseAttribute
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param ilDBInterface $database
     * @param ilObjCourse    $course
     */
    public function __construct(ilDBInterface $database, ilObjCourse $course)
    {
        parent::__construct($course);

        $this->database = $database;
        $this->metadata = $this->getMetadata();
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
            return $this->metadata;
        }

        if (self::COMPARABLE_VALUE_TYPE_STRING === $type) {
            return implode(',', $this->metadata);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getMetadata() : array
    {
        return $this->database->fetchAll(
            $this->database->queryF(
                "
                    SELECT m.keyword AS metadata FROM object_data AS d
                        JOIN il_meta_keyword AS m ON m.obj_id = d.obj_id
                        WHERE d.obj_id = %s;
                ",
                ['integer'],
                [$this->course->getId()]
            )
        );
    }
}