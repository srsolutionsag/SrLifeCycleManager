<?php

namespace srag\Plugins\SrLifeCycleManager\Repository;

use Generator;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGeneralRepository
{
    /**
     * Returns the parent id of the given object (ref-id) in the repository tree.
     *
     * @param int $ref_id
     * @return int
     */
    public function getParentId(int $ref_id) : int;

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

    /**
     * Returns all ILIAS users that match the given term (username, email, or other data).
     *
     * @param string $term
     * @return array<int, array<string, string>>
     */
    public function getUsersByTerm(string $term) : array;

    /**
     * Returns all available global roles as id => title pairs.
     *
     * @return array<int, string>
     */
    public function getAvailableGlobalRoles() : array;

    /**
     * Deletes an existing repository object for the given ref-id.
     *
     * This method primarily exists in order for PHPUnit to create a mock,
     * so the static method call to the ilRepUtil class can be emulated.
     *
     * @param int $ref_id
     * @return bool
     */
    public function deleteObject(int $ref_id) : bool;
}
