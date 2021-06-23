<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;

/**
 * Class ilSrLifeCycleManagerRepository is a factory for all
 * further plugin repositories.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrLifeCycleManagerRepository
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var \ILIAS\DI\RBACServices
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
     * prevents multiple instances
     */
    private function __clone() {}
    private function __wakeup() {}

    /**
     * ilSrLifeCycleManagerRepository constructor (private to prevent multiple instances)
     */
    private function __construct()
    {
        global $DIC;

        $this->rbac = $DIC->rbac();

        $this->routine_repository       = new ilSrRoutineRepository();
        $this->notification_repository  = new ilSrNotificationRepository();
        $this->rule_repository          = new ilSrRuleRepository();
    }

    /**
     * returns a singleton instance of this repository factory.
     *
     * @return self
     */
    public static function getInstance() : self
    {
        if (!isset(self::$instance)) self::$instance = new self();
        return self::$instance;
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
     * Returns all available global-roles as 'id' => 'title' paris.
     *
     * @return array
     */
    public function getGlobalRoleOptions() : array
    {
        $role_options = [];
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);

        if (empty($global_roles)) return $role_options;

        foreach ($global_roles as $role_data) {
            $role_id    = (int) $role_data['obj_id'];
            $role_title = ilObjRole::_getTranslation($role_data['title']);

            $role_options[$role_id] = $role_title;
        }

        return $role_options;
    }
}