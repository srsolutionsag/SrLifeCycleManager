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
class ObjectTaxonomy extends ObjectAttribute
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
            self::COMPARABLE_VALUE_TYPE_ARRAY => $this->getTaxonomies(),
            self::COMPARABLE_VALUE_TYPE_STRING => implode(',', $this->getTaxonomies()),
            default => null,
        };
    }

    /**
     * @return string[]
     */
    protected function getTaxonomies(): array
    {
        $obj_id = $this->getObject()->getId();

        if (isset(self::$cache[$obj_id])) {
            return self::$cache[$obj_id];
        }

        $taxonomies = $this->database->fetchAll(
            $this->database->queryF(
                "
                    SELECT tn.title FROM tax_node_assignment AS ta
                        JOIN tax_node AS tn ON tn.obj_id = ta.node_id
                        WHERE ta.obj_id = %s
                    ;
                ",
                ['integer'],
                [$obj_id]
            )
        );

        foreach ($taxonomies as $taxonomy_result) {
            self::$cache[$obj_id][] = $taxonomy_result['title'];
        }

        return self::$cache[$obj_id];
    }
}
