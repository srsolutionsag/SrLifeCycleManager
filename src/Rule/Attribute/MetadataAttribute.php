<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use ilDBInterface;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class MetadataAttribute implements IAttribute
{
    /**
     * @var array<int, string[]>
     */
    protected static $cache = [];

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
                return $this->getMetadata($this->getDatabase(), $this->getObject());

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->getMetadata($this->getDatabase(), $this->getObject()));

            default:
                return null;
        }
    }

    /**
     * @param ilDBInterface $database
     * @param int           $obj_id
     * @return string[]
     */
    protected function getMetadata(ilDBInterface $database, ilObject $object) : array
    {
        $obj_id = $object->getId();

        if (isset(self::$cache[$obj_id])) {
            return self::$cache[$obj_id];
        }

        $metadata = $database->fetchAll(
            $database->queryF(
                "
                    SELECT m.keyword FROM object_data AS d
                        JOIN il_meta_keyword AS m ON m.obj_id = d.obj_id
                        WHERE d.obj_id = %s
                    ;
                ",
                ['integer'],
                [$obj_id]
            )
        );

        foreach ($metadata as $metadata_result) {
            self::$cache[$obj_id][] = $metadata_result['keyword'];
        }

        return self::$cache[$obj_id];
    }

    /**
     * Must return the database instance.
     */
    abstract protected function getDatabase(): ilDBInterface;

    /**
     * Must return the object of this attribute.
     */
    abstract protected function getObject(): ilObject;
}