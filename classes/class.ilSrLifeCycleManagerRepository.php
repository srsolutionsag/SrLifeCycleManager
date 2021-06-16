<?php

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;

/**
 * Class ilSrLifeCycleManagerRepository
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
        $this->routine_repository       = new ilSrRoutineRepository();
        $this->notification_repository  = new ilSrNotificationRepository();
        $this->rule_repository          = new ilSrRuleRepository();
    }

    /**
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
}