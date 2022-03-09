<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * This trait contains a set of helper functions that are used
 * within multiple repositories.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
trait ilSrRepositoryHelper
{
    /**
     * @inheritDoc
     */
    public function getRepositoryObjects(int $ref_id = 1) : Generator
    {
        $container_objects = $this->tree->getChildsByTypeFilter($ref_id, ['crs', 'cat', 'grp', 'fold']);
        if (empty($container_objects)) {
            yield new EmptyIterator();
        }

        foreach ($container_objects as $container) {
            if (in_array($container['type'], IRoutine::ROUTINE_TYPES, true)) {
                try {
                    yield ilObjectFactory::getInstanceByRefId((int) $container['ref_id']);
                } catch (Exception $exception) {
                    continue;
                }
            } else {
                yield from $this->getRepositoryObjects((int) $container['ref_id']);
            }
        }
    }

    /**
     * Returns all parent ids for the given object (ref-id). Result
     * DOES NOT contain the object itself.
     *
     * @param int $ref_id
     * @return array|null
     */
    protected function getParentIdsRecursively(int $ref_id) : ?array
    {
        static $parents;

        $parent_id = $this->tree->getParentId($ref_id);
        // type-cast is not redundant, as getParentId() returns
        // a string (not as stated by the phpdoc).
        if (null !== $parent_id && 0 < (int) $parent_id) {
            $parents[] = (int) $parent_id;
            $this->getParentIdsRecursively((int) $parent_id);
        }

        return (!empty($parents)) ? $parents : null;
    }

    /**
     * Returns a list of parent id's INCLUDING the given object (ref-id)
     * that is compatible for an SQL "IN" comparison.
     *
     * @param int $ref_id
     * @return string
     */
    protected function getParentIdsForSqlComparison(int $ref_id) : string
    {
        // gather all parent objects of the given ref-id and
        // add the id itself to the array as well.
        $parents = $this->getParentIdsRecursively($ref_id);
        $parents[] = $ref_id;

        return implode(',', $parents);
    }
}