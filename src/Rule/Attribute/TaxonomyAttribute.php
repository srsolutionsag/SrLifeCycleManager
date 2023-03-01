<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use ilDBInterface;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class TaxonomyAttribute implements IAttribute
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
                return $this->getTaxonomies($this->getDatabase(), $this->getObject());

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->getTaxonomies($this->getDatabase(), $this->getObject()));

            default:
                return null;
        }
    }

    /**
     * @param ilDBInterface $database
     * @param int           $obj_id
     * @return string[]
     */
    protected function getTaxonomies(ilDBInterface $database, ilObject $obect) : array
    {
        $obj_id = $obect->getId();

        if (isset(self::$cache[$obj_id])) {
            return self::$cache[$obj_id];
        }

        $taxonomies = $database->fetchAll(
            $database->queryF(
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


    /**
     * Must return the database instance.
     */
    abstract protected function getDatabase(): ilDBInterface;

    /**
     * Must return the object of this attribute.
     */
    abstract protected function getObject(): ilObject;
}