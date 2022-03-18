<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

use ilDBInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait MetadataHelper
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
    protected function getMetadata(ilDBInterface $database, int $obj_id) : array
    {
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
}