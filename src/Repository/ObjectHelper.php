<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Repository;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use Exception;
use Generator;
use ilObjectFactory;
use ilTree;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait ObjectHelper
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @inheritdoc
     */
    public function getParentId(int $ref_id): int
    {
        // type-cast is necessary, the phpdoc comment is wrong.
        return (int) $this->tree->getParentId($ref_id);
    }

    /**
     * Returns all parent ids for the given object (ref-id). Result
     * DOES NOT contain the object itself.
     *
     * @param int $ref_id
     * @return array|null
     */
    protected function getParentIdsRecursively(int $ref_id): array
    {
        if (1 === $ref_id) {
            return [];
        }

        $parents_ref_ids = [];
        while (0 !== ($ref_id = $this->getParentId($ref_id))) {
            $parents_ref_ids[] = $ref_id;
        }

        return $parents_ref_ids;
    }

    /**
     * Returns a list of parent id's INCLUDING the given object (ref-id)
     * that is compatible for an SQL "IN" comparison.
     *
     * @param int $ref_id
     * @return string
     */
    protected function getParentIdsForSqlComparison(int $ref_id): string
    {
        // gather all parent objects of the given ref-id and
        // add the id itself to the array as well.
        $parents = $this->getParentIdsRecursively($ref_id);
        $parents[] = $ref_id;

        return implode(',', $parents);
    }

    /**
     * Returns the parent id of the given object (ref-id).
     *
     * If the parent id cannot be located, 0 is returned so that the
     * comparison will not deliver any results.
     *
     * @param int $ref_id
     * @return string
     */
    protected function getParentIdForSqlComparison(int $ref_id): string
    {
        return (string) ($this->tree->getParentId($ref_id) ?? 0);
    }

    /**
     * Returns all repository object types which can hold further
     * objects (children).
     *
     * @return string[]
     */
    protected function getContainerObjectTypes(): array
    {
        return ['crs', 'cat', 'grp', 'fold', 'itgr'];
    }

    /**
     * Returns all repository object types which support participants.
     *
     * @return string[]
     */
    protected function getSupportedParticipantTypes(): array
    {
        return ['crs', 'grp', 'lso', 'sess'];
    }
}
