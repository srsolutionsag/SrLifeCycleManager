<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use ILIAS\DI\RBACServices;

/**
 * Class ilSrLifeCycleManagerRepository is a factory for all
 * further plugin repositories.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ilSrLifeCycleManagerRepository
{
    /**
     * @var RBACServices
     */
    private $rbac;

    /**
     * @var IRoutineRepository
     */
    private $routine_repository;

    /**
     * @var INotificationRepository;
     */
    private $notification_repository;

    /**
     * @var IRuleRepository
     */
    private $rule_repository;

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
        $this->rbac = $rbac;
        $this->rule_repository = new ilSrRuleRepository($database);
        $this->notification_repository = new ilSrNotificationRepository();
        $this->routine_repository = new ilSrRoutineRepository(
            $this->rule_repository,
            $this->notification_repository,
            $tree
        );
    }

    /**
     * @return IRoutineRepository
     */
    public function routine() : IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @return INotificationRepository
     */
    public function notification() : INotificationRepository
    {
        return $this->notification_repository;
    }

    /**
     * @return IRuleRepository
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