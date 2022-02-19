<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use ILIAS\DI\RBACServices;
use srag\Plugins\SrLifeCycleManager\IRepository;

/**
 * Class ilSrLifeCycleManagerRepository is a factory for all
 * further plugin repositories.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrLifeCycleManagerRepository implements IRepository
{
    /**
     * @var RBACServices
     */
    protected $rbac;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationRepository;
     */
    protected $notification_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @param ilDBInterface $database
     * @param RBACServices  $rbac
     * @param ilTree        $tree
     */
    public function __construct(
        ilDBInterface $database,
        RBACServices $rbac,
        ilTree $tree
    ) {
        $this->rule_repository = new ilSrRuleRepository();
        $this->notification_repository = new ilSrNotificationRepository();
        $this->routine_repository = new ilSrRoutineRepository($tree);
        $this->database = $database;
        $this->rbac = $rbac;
    }

    /**
     * @inheritDoc
     */
    public function routine() : IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @inheritDoc
     */
    public function notification() : INotificationRepository
    {
        return $this->notification_repository;
    }

    /**
     * @inheritDoc
     */
    public function rule() : IRuleRepository
    {
        return $this->rule_repository;
    }

    /**
     * Returns all available global-roles as 'id' => 'title' pairs.
     *
     * This method is used for UI input options, therefore this array
     * DOES NOT contain the administrator global role, as it should
     * always be able to everything.
     *
     * @return array
     */
    public function getGlobalRoleOptions() : array
    {
        $role_options = [];
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);

        if (empty($global_roles)) return $role_options;

        foreach ($global_roles as $role_data) {
            $role_id = (int) $role_data['obj_id'];
            // the administrator role can be ignored, as this
            // role should always be able to do everything.
            if ((int) SYSTEM_ROLE_ID !== $role_id) {
                $role_title = ilObjRole::_getTranslation($role_data['title']);

                // map the role-title to it's role id associatively.
                $role_options[$role_id] = $role_title;
            }
        }

        return $role_options;
    }
}