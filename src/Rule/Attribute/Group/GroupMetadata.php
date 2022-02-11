<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group;

use ilDBInterface;
use ilObjGroup;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupMetadata extends GroupAttribute
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param ilDBInterface $database
     * @param ilObjGroup    $group
     */
    public function __construct(ilDBInterface $database, ilObjGroup $group)
    {
        parent::__construct($group);

        $this->database = $database;
        $this->metadata = $this->getMetadata();
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
                return $this->metadata;

            case self::COMPARABLE_VALUE_TYPE_STRING:
                return implode(',', $this->metadata);

            default:
                return null;
        }
    }

    /**
     * @return array
     */
    protected function getMetadata() : array
    {
        return $this->database->fetchAll(
            $this->database->queryF(
                "
                    SELECT m.keyword AS metadata FROM object_data AS d
                        JOIN il_meta_keyword AS m ON m.obj_id = d.obj_id
                        WHERE d.obj_id = %s;
                ",
                ['integer'],
                [$this->group->getId()]
            )
        );
    }
}