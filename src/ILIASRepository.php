<?php

namespace srag\Plugins\SrLifeCycleManager;

use Generator;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ILIASRepository
{
    /**
     * Returns all repository objects that can be deleted by a routine
     * starting from the given ref-id.
     *
     * @return Generator|ilObject[]
     */
    public function getRepositoryObjects() : Generator;

    /**
     * Returns all repository objects that relate to the given term.
     *
     * @param string $term
     * @return array<int, array<string, string>>
     */
    public function getObjectsByTerm(string $term) : array;
}