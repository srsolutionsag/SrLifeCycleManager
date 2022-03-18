<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use ilDBInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait TaxonomyHelper
{
    /**
     * @var array<int, string[]>
     */
    protected static $cache = [];

    /**
     * @param ilDBInterface $database
     * @param int           $obj_id
     * @return string[]
     */
    protected function getTaxonomies(ilDBInterface $database, int $obj_id) : array
    {
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
}