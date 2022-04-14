<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Repository;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use Iterator;
use Exception;
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
     * @inheritDoc
     */
    public function getRepositoryObjects(int $ref_id = 1) : Iterator
    {
        $container_objects = $this->tree->getChildsByTypeFilter($ref_id, ['crs', 'cat', 'grp', 'fold']);
        if (empty($container_objects)) {
            yield null;
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
     * @inheritdoc
     */
    public function getParentId(int $ref_id) : int
    {
        return (int) $this->tree->getParentId($ref_id);
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

    /**
     * Returns the parent id of the given object (ref-id).
     *
     * If the parent id cannot be located, 0 is returned so that the
     * comparison will not deliver any results.
     *
     * @param int $ref_id
     * @return string
     */
    protected function getParentIdForSqlComparison(int $ref_id) : string
    {
        return (string) ($this->tree->getParentId($ref_id) ?? 0);
    }
}