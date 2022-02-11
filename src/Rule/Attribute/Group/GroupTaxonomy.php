<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use ilDBInterface;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupTaxonomy extends GroupAttribute
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
     * @param ilObjGroup    $group
     */
    public function __construct(ilDBInterface $database, ilObjGroup $group)
    {
        parent::__construct($group);

        $this->database = $database;
        $this->taxonomies = $this->getTaxonomies();
    }

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
                return $this->taxonomies;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->taxonomies);

            default:
                return null;
        }
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
                [$this->group->getId()]
            )
        );
    }
}