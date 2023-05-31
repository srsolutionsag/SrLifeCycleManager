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
    public function getParentId(int $ref_id): int;

    /**
     * Returns the object (ilObject) for the given ref-id.
     */
    public function getObject(int $ref_id): ?ilObject;

    /**
     * Returns the participant object for the given object, if it supports it.
     *
     * @param ilObject $object
     * @return \ilParticipants|null
     */
    public function getParticipantObject(ilObject $object): ?\ilParticipants;

    /**
     * Returns all repository objects which are of the given types.
     *
     * @param string[] $types object types to retrieve
     * @return Generator|ilObject[]
     */
    public function getRepositoryObjects(int $ref_id, array $types): Generator;

    /**
     * Returns all repository objects that relate to the given term.
     *
     * @return array<int, array<string, string>>
     */
    public function getObjectsByTypeAndTerm(string $type, string $term): array;

    /**
     * Returns all ILIAS users that match the given term (username, email, or other data).
     *
     * @param string $term
     * @return array<int, array<string, string>>
     */
    public function getUsersByTerm(string $term): array;

    /**
     * Returns all available global roles as id => title pairs.
     *
     * @return array<int, string>
     */
    public function getAvailableGlobalRoles(): array;

    /**
     * Deletes an existing repository object for the given ref-id.
     *
     * This method primarily exists in order for PHPUnit to create a mock,
     * so the static method call to the ilRepUtil class can be emulated.
     *
     * @param int $ref_id
     * @return bool
     */
    public function deleteObject(int $ref_id): bool;
}
