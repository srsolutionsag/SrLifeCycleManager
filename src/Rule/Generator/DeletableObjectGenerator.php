<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Generator;

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Comparison;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\IRepository;
use ilObject;

/**
 * This generator yields objects that are considered deletable.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Deletable objects must be affected by at least one routine of
 * which all rules are applicable.
 *
 * To enable some flexibility this generator accepts another generator
 * that yields repository objects, so that specific objects could be
 * filtered.
 */
class DeletableObjectGenerator implements IDeletableObjectGenerator
{
    /**
     * @var IDeletableObject|null
     */
    protected $current_object;

    /**
     * @var IObjectGenerator
     */
    protected $object_generator;

    /**
     * @var RequirementFactory
     */
    protected $requirement_factory;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * @param IObjectGenerator   $object_generator
     * @param RequirementFactory $requirement_factory
     * @param AttributeFactory   $attribute_factory
     * @param IRepository        $repository
     */
    public function __construct(
        IObjectGenerator $object_generator,
        RequirementFactory $requirement_factory,
        AttributeFactory $attribute_factory,
        IRepository $repository
    ) {
        $this->object_generator = $object_generator;
        $this->requirement_factory = $requirement_factory;
        $this->attribute_factory = $attribute_factory;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function current() : ?IDeletableObject
    {
        return $this->current_object;
    }

    /**
     * @inheritDoc
     */
    public function key() : ?int
    {
        // use the current objects ref-id as key if possible.
        if (null !== $this->current_object) {
            return $this->current_object->getInstance()->getRefId();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        $this->object_generator->rewind();
        $this->current_object = null;
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        $this->object_generator->next();
        $this->current_object = null;
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        $object = $this->object_generator->current();

        // this iterator is finished when the generator is.
        if (null === $object) {
            return false;
        }

        // if the current object is not deletable, advance the generator
        // and check if the next available object is.
        $affected_routines = $this->getAffectedRoutines($object);
        if (empty($affected_routines)) {
            $this->next();
            return $this->valid();
        }

        // otherwise, initialize the current deletable object.
        $this->current_object = new DeletableObject(
            $object,
            $affected_routines
        );

        return true;
    }

    /**
     * Returns all routines that affect the given object
     *
     * @param ilObject $object
     * @return IRoutine[]
     */
    protected function getAffectedRoutines(ilObject $object) : array
    {
        $affected_by = [];
        foreach ($this->repository->routine()->getAllByRefId($object->getRefId()) as $routine) {
            // skip inactive routines.
            if (!$routine->isActive()) {
                continue;
            }

            $all_rules_applicable = true;
            foreach ($this->repository->rule()->getByRoutine($routine) as $rule) {
                $comparison = new Comparison(
                    $this->attribute_factory,
                    $this->requirement_factory->getRequirement($object),
                    $rule
                );

                if (!$comparison->isApplicable()) {
                    $all_rules_applicable = false;
                }
            }

            if ($all_rules_applicable) {
                $affected_by[] = $routine;
            }
        }

        return $affected_by;
    }
}