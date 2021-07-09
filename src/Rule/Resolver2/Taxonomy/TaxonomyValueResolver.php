<?php

namespace srag\Plugins\SrLifeCycleManager\Rule\Resolver\Taxonomy;

use srag\Plugins\SrLifeCycleManager\Rule\Comparison\IComparison;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\Taxonomy\ITaxonomyAware;
use srag\Plugins\SrLifeCycleManager\Rule\Resolver\IValueResolver;

/**
 * Class TaxonomyValueResolver
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
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

        return $this->resolveTaxonomyAttribute(
            $comparison->getObject(),
            $comparison->getRule()->getLhsValue()
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

        return $this->resolveTaxonomyAttribute(
            $comparison->getObject(),
            $comparison->getRule()->getRhsValue()
        );
    }

    /**
     * returns the given attribute of all existing taxonomies assigned to the given object.
     *
     * @param \ilObject $object
     * @param string    $attribute
     * @return array|mixed|null
     */
    public function resolveTaxonomyAttribute(\ilObject $object, string $attribute)
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

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return [
            self::ATTRIBUTE_ID,
            self::ATTRIBUTE_TITLE,
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateValue($value) : bool
    {
        return in_array($value, $this->getAttributes());
    }

    /**
     * returns all taxonomies assigned to the given object id.
     *
     * @param int $obj_id
     * @return array|null
     */
    private function getTaxonomiesForObjId(int $obj_id) : ?array
    {
        $query = "
            SELECT node.obj_id AS id, node.title AS title FROM tax_node_assignment AS assignment
                JOIN tax_node AS node ON node.obj_id = assignment.node_id
                WHERE assignment.obj_id = %s;
        ";

        $results = $this->database->fetchAll(
            $this->database->queryF($query, ['integer'], [$obj_id]),
            \ilDBConstants::FETCHMODE_ASSOC
        );

        return (!empty($results)) ? $results : null;
    }
}