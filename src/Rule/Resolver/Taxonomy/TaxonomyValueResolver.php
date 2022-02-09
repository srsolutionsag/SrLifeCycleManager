<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Taxonomy;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\IValueResolver;

/**
 * Class TaxonomyValueResolver
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @package srag\Plugins\SrLifeCycleManager\Rule\Resolver\Course
 */
final class TaxonomyValueResolver implements IValueResolver
{
    /**
     * @var string resolver value type
     */
    public const VALUE_TYPE = 'taxonomy';

    /**
     * possible attributes
     */
    public const ATTRIBUTE_ID       = 'id';
    public const ATTRIBUTE_TITLE    = 'title';

    /**
     * @var \ilDBInterface
     */
    private $database;
    /**
     * @var array
     */
    private $taxonomy_cache = [];

    /**
     * TaxonomyValueResolver constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->database = $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function resolveLhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof ITaxonomyAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not taxonomy-aware.");
        }

        return $this->resolveSelectedTaxonomyNodes(
            $comparison->getObject(),
            (int)$comparison->getRule()->getLhsValue()
        );
    }

    /**
     * @inheritDoc
     */
    public function resolveRhsValue(IComparison $comparison)
    {
        if (!$comparison instanceof ITaxonomyAware) {
            throw new \LogicException("Comparison '[$comparison::class]' is not taxonomy-aware.");
        }

        return $this->resolveSelectedTaxonomyNodes(
            $comparison->getObject(),
            (int)$comparison->getRule()->getRhsValue()
        );
    }

    /**
     * returns the given attribute of all existing taxonomies assigned to the given object.
     *
     * @param \ilObject $object
     * @param string    $attribute
     * @return array|mixed|null
     */
    public function resolveTaxonomyAttribute(\ilObject $object, string $attribute = 'title')
    {
        $taxonomies = $this->getTaxonomiesForObjId($object->getId());
        if (null === $taxonomies) return null;

        $resolved_data = [];
        foreach ($taxonomies as $taxonomy_data) {
            $resolved_data[] = $taxonomy_data[$attribute];
        }

        // return resolved data as non-array value if possible
        return $resolved_data;
    }

    public function resolveSelectedTaxonomyNodes(\ilObject $object, int $taxonomy_id)
    {
        $taxonomies = $this->getTaxonomiesForObjId($object->getId());
        if (null === $taxonomies) return null;
        $resolved_data = [];
        foreach ($taxonomies as $taxonomy_data) {
            if ((int)$taxonomy_data['tax_id'] !== $taxonomy_id) {
                continue;
            }
            $resolved_data[] = $taxonomy_data['title'];
        }

        // return resolved data as non-array value if possible
        return $resolved_data;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function validateValue($value) : bool
    {
        return \ilObjTaxonomy::_exists((int)$value);
    }

    /**
     * returns all taxonomies assigned to the given object id.
     *
     * @param int $obj_id
     * @return array|null
     */
    private function getTaxonomiesForObjId(int $obj_id) : ?array
    {
        if (isset($this->taxonomy_cache[$obj_id])) {
            return $this->taxonomy_cache[$obj_id];
        }

        $query = "
            SELECT node.obj_id AS id, node.title AS title, assignment.tax_id FROM tax_node_assignment AS assignment
                JOIN tax_node AS node ON node.obj_id = assignment.node_id
                WHERE assignment.obj_id = %s;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF($query, ['integer'], [$obj_id]),
            \ilDBConstants::FETCHMODE_ASSOC
        );

        $this->taxonomy_cache[$obj_id] = $results;

        return (!empty($results)) ? $results : null;
    }
}
