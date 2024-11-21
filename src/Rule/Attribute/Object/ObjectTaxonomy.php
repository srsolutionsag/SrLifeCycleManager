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

use ilDBInterface;
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

    protected \ilDBInterface $database;

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
     * @return mixed[]|string|null
     */
    public function getComparableValue(string $type)
    {
        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_ARRAY:
                return $this->getTaxonomies();

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->getTaxonomies());

            default:
                return null;
        }
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
