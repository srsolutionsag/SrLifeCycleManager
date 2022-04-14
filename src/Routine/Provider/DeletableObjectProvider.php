<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Routine\Provider;

use Iterator;

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
class DeletableObjectProvider implements IDeletableObjectProvider
{
    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @var Iterator
     */
    protected $object_iterator;

    /**
     * @var IDeletableObject|null
     */
    protected $current_object;

    /**
     * @param RoutineProvider $routine_provider
     * @param Iterator        $object_iterator
     */
    public function __construct(
        RoutineProvider $routine_provider,
        Iterator $object_iterator
    ) {
        $this->routine_provider = $routine_provider;
        $this->object_iterator = $object_iterator;
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
        $this->object_iterator->rewind();
        $this->current_object = null;
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        $this->object_iterator->next();
        $this->current_object = null;
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        $object = $this->object_iterator->current();

        // this iterator is finished when the generator is.
        if (null === $object) {
            return false;
        }

        // if the current object is not deletable, advance the generator
        // and check if the next available object is.
        $affected_routines = $this->routine_provider->getAffectingRoutines($object);
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
}