<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\TaxonomyAttribute;
use ilDBInterface;
use ilObjGroup;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupTaxonomy extends TaxonomyAttribute
{
    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var ilObjGroup
     */
    private $object;

    /**
     * @param ilDBInterface $database
     * @param ilObjGroup    $group
     */
    public function __construct(ilDBInterface $database, ilObjGroup $group)
    {
        $this->database = $database;
        $this->object = $group;
    }

    /**
     * @inheritDoc
     */
    protected function getDatabase(): ilDBInterface
    {
        return $this->database;
    }

    /**
     * @inheritDoc
     */
    protected function getObject(): ilObject
    {
        return $this->object;
    }
}