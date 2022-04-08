<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\ILIASRepository;
use ILIAS\DI\RBACServices;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerRepository implements ILIASRepository
{
    use ilSrRepositoryHelper;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

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
    public function getObjectsByTerm(string $term) : array
    {
        $term  = htmlspecialchars($term);
        $term  = $this->database->quote("%$term%", 'text');
        $query = "
            SELECT ref.ref_id AS value, obj.title AS display, obj.title AS searchby FROM object_data AS obj
		        LEFT JOIN object_translation AS trans ON trans.obj_id = obj.obj_id
                LEFT JOIN object_reference AS ref ON ref.obj_id = obj.obj_id
		        WHERE obj.title LIKE $term 
		        OR trans.title LIKE $term
            ;
		";

        return $this->database->fetchAll(
            $this->database->query($query)
        );
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
}