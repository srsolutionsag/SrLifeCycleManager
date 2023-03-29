<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Repository\IGeneralRepository;
use srag\Plugins\SrLifeCycleManager\Repository\ObjectHelper;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\DI\RBACServices;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrGeneralRepository implements IGeneralRepository
{
    use ObjectHelper;

    /**
     * This variable holds the merged object types of container
     * objects and routine-types, due to the recursive object
     * retrieval: @see self::getRepositoryObjects()
     *
     * @var string[]
     */
    protected $routine_candidate_and_container_types;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var RBACServices
     */
    protected $rbac;

    /**
     * @param ilDBInterface $database
     * @param ilTree        $tree
     * @param RBACServices  $rbac
     */
    public function __construct(ilDBInterface $database, ilTree $tree, RBACServices $rbac)
    {
        $this->routine_candidate_and_container_types = array_unique(
            array_merge(
                $this->getContainerObjectTypes(),
                IRoutine::ROUTINE_TYPES,
            )
        );

        $this->database = $database;
        $this->tree = $tree;
        $this->rbac = $rbac;
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryObjects(int $ref_id = 1): Generator
    {
        $routine_candidates_and_container = $this->tree->getChildsByTypeFilter(
            $ref_id,
            $this->routine_candidate_and_container_types
        );

        if (empty($routine_candidates_and_container)) {
            return;
        }

        foreach ($routine_candidates_and_container as $candidate_or_container) {
            $candidate_or_container_ref_id = (int) $candidate_or_container['ref_id'];
            if (in_array($candidate_or_container['type'], IRoutine::ROUTINE_TYPES, true)) {
                try {
                    yield $candidate_or_container_ref_id => ilObjectFactory::getInstanceByRefId(
                        $candidate_or_container_ref_id
                    );
                } catch (Exception $e) {
                    continue;
                }
            } else {
                yield from $this->getRepositoryObjects($candidate_or_container_ref_id);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectsByTypeAndTerm(string $type, string $term): array
    {
        $term = htmlspecialchars($term);
        $type = htmlspecialchars($type);
        $term = $this->database->quote("%$term%", 'text');
        $query = "
            SELECT ref.ref_id AS value, obj.title AS display, obj.title AS searchby FROM object_data AS obj
		        LEFT JOIN object_translation AS trans ON trans.obj_id = obj.obj_id
                LEFT JOIN object_reference AS ref ON ref.obj_id = obj.obj_id
		        WHERE obj.type = '$type'
		        AND (
                    obj.title LIKE $term 
		            OR trans.title LIKE $term		        
		        )
            ;
		";

        return $this->database->fetchAll(
            $this->database->query($query)
        );
    }

    /**
     * @inheritDoc
     */
    public function getUsersByTerm(string $term) : array
    {
        $users = [];
        foreach (ilObjUser::searchUsers($term) as $user_data) {
            $beautified_name   = "{$user_data['login_name']} ({$user_data['usr_id']})";
            $users[] = [
                'value'    => $user_data['usr_id'],
                'searchBy' => $beautified_name,
                'display'  => $beautified_name,
            ];
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableGlobalRoles() : array
    {
        $role_options = [];
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);
        if (empty($global_roles)) {
            return $role_options;
        }

        foreach ($global_roles as $role_data) {
            $role_id = (int) $role_data['obj_id'];
            // the administrator role can be ignored, as this
            // role should always be able to do everything.
            if ((int) SYSTEM_ROLE_ID !== $role_id) {
                $role_title = ilObjRole::_getTranslation($role_data['title']);

                // map the role-title to its role id associatively.
                $role_options[$role_id] = $role_title;
            }
        }

        return $role_options;
    }

    /**
     * @inheritDoc
     */
    public function deleteObject(int $ref_id): bool
    {
        try {
            ilRepUtil::deleteObjects(null, [$ref_id]);
            return true;
        } catch (ilRepositoryException $e) {
            return false;
        }
    }
}