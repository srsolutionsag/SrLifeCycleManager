<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

use ilDBInterface;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectMetadata extends ObjectAttribute
{
    /**
     * @var array<int, string[]>
     */
    protected static $cache = [];

    /**
     * @var ilDBInterface
     */
    protected $database;

    public function __construct(ilDBInterface $database, ilObject $object)
    {
        parent::__construct($object);

        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
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
                return $this->getMetadata();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->getMetadata());

            default:
                return null;
        }
    }

    /**
     * @return string[]
     */
    protected function getMetadata(): array
    {
        $obj_id = $this->getObject()->getId();

        if (isset(self::$cache[$obj_id])) {
            return self::$cache[$obj_id];
        }

        $metadata = $this->database->fetchAll(
            $this->database->queryF(
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
}
