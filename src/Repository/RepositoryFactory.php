<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Repository;

use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RepositoryFactory
{
    /**
     * @var IGeneralRepository
     */
    protected $general_repository;

    /**
     * @var IConfigRepository
     */
    protected $config_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var IRoutineAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @var INotificationRepository
     */
    protected $notification_repository;

    /**
     * @var IWhitelistRepository
     */
    protected $whitelist_repository;

    /**
     * @param IGeneralRepository $general_repository
     * @param IConfigRepository $config_repository
     * @param IRoutineRepository $routine_repository
     * @param IRoutineAssignmentRepository $assignment_repository
     * @param IRuleRepository $rule_repository
     * @param INotificationRepository $notification_repository
     * @param IWhitelistRepository $whitelist_repository
     */
    public function __construct(
        IGeneralRepository $general_repository,
        IConfigRepository $config_repository,
        IRoutineRepository $routine_repository,
        IRoutineAssignmentRepository $assignment_repository,
        IRuleRepository $rule_repository,
        INotificationRepository $notification_repository,
        IWhitelistRepository $whitelist_repository
    ) {
        $this->general_repository = $general_repository;
        $this->config_repository = $config_repository;
        $this->routine_repository = $routine_repository;
        $this->assignment_repository = $assignment_repository;
        $this->rule_repository = $rule_repository;
        $this->notification_repository = $notification_repository;
        $this->whitelist_repository = $whitelist_repository;
    }

    /**
     * @return IGeneralRepository
     */
    public function general() : IGeneralRepository
    {
        return $this->general_repository;
    }

    /**
     * @return IConfigRepository
     */
    public function config() : IConfigRepository
    {
        return $this->config_repository;
    }

    /**
     * @return IRoutineAssignmentRepository
     */
    public function assignment() : IRoutineAssignmentRepository
    {
        return $this->assignment_repository;
    }

    /**
     * @return IRoutineRepository
     */
    public function routine() : IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @return IRuleRepository
     */
    public function rule() : IRuleRepository
    {
        return $this->rule_repository;
    }

    /**
     * @return INotificationRepository
     */
    public function notification() : INotificationRepository
    {
        return $this->notification_repository;
    }

    /**
     * @return IWhitelistRepository
     */
    public function whitelist() : IWhitelistRepository
    {
        return $this->whitelist_repository;
    }
}