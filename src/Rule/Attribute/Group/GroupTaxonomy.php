<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\TaxonomyHelper;
use ilDBInterface;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupTaxonomy extends GroupAttribute
{
    use TaxonomyHelper;

    /**
     * @var string[]
     */
    protected $taxonomies;

    /**
     * @param ilDBInterface $database
     * @param ilObjGroup    $group
     */
    public function __construct(ilDBInterface $database, ilObjGroup $group)
    {
        parent::__construct($group);

        $this->taxonomies = $this->getTaxonomies(
            $database,
            $group->getId()
        );
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
}