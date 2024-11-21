<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

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

    public function __construct(protected \ilDBInterface $database, ilObject $object)
    {
        parent::__construct($object);
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
     * @return mixed[]|string|null
     */
    public function getComparableValue(string $type): array|string|null
    {
        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_ARRAY => $this->getMetadata(),
            self::COMPARABLE_VALUE_TYPE_STRING => implode(',', $this->getMetadata()),
            default => null,
        };
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
