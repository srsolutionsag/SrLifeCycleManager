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
        $this->database = $database;
        $this->tree = $tree;
        $this->rbac = $rbac;
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $ref_id): ?ilObject
    {
        $object = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (false !== $object) {
            return $object;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getParticipantObject(ilObject $object): ?\ilParticipants
    {
        if (!in_array($object->getType(), $this->getSupportedParticipantTypes(), true)) {
            return null;
        }

        try {
            return ilParticipants::getInstance($object->getRefId());
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryObjectChildren(
        int $ref_id,
        array $types,
        int $max_depth = PHP_INT_MAX,
        int $depth = 0
    ): Generator {
        if ($depth === $max_depth) {
            return;
        }

        $container_types = $this->getContainerObjectTypes();
        $combined_types = array_unique(array_merge($container_types, $types));

        $children = $this->tree->getChildsByTypeFilter($ref_id, $combined_types);

        foreach ($children as $container_or_candidate) {
            $object_ref_id = (int) $container_or_candidate['ref_id'];

            if (in_array($container_or_candidate['type'], $types, true)) {
                $object = ilObjectFactory::getInstanceByRefId($object_ref_id, false);
                if (false !== $object) {
                    yield $object_ref_id => $object;
                }

                continue;
            }

            // object is a container object at this point.
            yield from $this->getRepositoryObjectChildren($object_ref_id, $types, $max_depth, $depth + 1);
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectsByTypeAndTerm(
        string $type,
        string $term
    ): array {
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
    public function getUsersByTerm(string $term): array
    {
        $query = new ilUserQuery();
        $query->setTextFilter($term);
        $results = $query->query();

        $users = [];
        foreach ($results['set'] as $user_data) {
            $beautified_name = "{$user_data['login']} ({$user_data['usr_id']})";
            $users[] = [
                'value' => $user_data['usr_id'],
                'searchBy' => $beautified_name,
                'display' => $beautified_name,
            ];
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableGlobalRoles(): array
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
            ilRepUtil::deleteObjects(0, [$ref_id]);
            return true;
        } catch (ilRepositoryException $e) {
            return false;
        }
    }
}
