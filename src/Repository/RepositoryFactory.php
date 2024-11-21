<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Repository;

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RepositoryFactory
{
    /**
     * @param IGeneralRepository $general_repository
     * @param IConfigRepository $config_repository
     * @param IRoutineRepository $routine_repository
     * @param IRoutineAssignmentRepository $assignment_repository
     * @param IRuleRepository $rule_repository
     * @param IConfirmationRepository $confirmation_repository
     * @param IReminderRepository $reminder_repository
     * @param IWhitelistRepository $whitelist_repository
     * @param ITokenRepository $token_repository
     */
    public function __construct(
        protected IGeneralRepository $general_repository,
        protected IConfigRepository $config_repository,
        protected IRoutineRepository $routine_repository,
        protected IRoutineAssignmentRepository $assignment_repository,
        protected IRuleRepository $rule_repository,
        protected IConfirmationRepository $confirmation_repository,
        protected IReminderRepository $reminder_repository,
        protected IWhitelistRepository $whitelist_repository,
        protected ITokenRepository $token_repository
    ) {
    }

    /**
     * @return IGeneralRepository
     */
    public function general(): IGeneralRepository
    {
        return $this->general_repository;
    }

    /**
     * @return IConfigRepository
     */
    public function config(): IConfigRepository
    {
        return $this->config_repository;
    }

    /**
     * @return IRoutineAssignmentRepository
     */
    public function assignment(): IRoutineAssignmentRepository
    {
        return $this->assignment_repository;
    }

    /**
     * @return IRoutineRepository
     */
    public function routine(): IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @return IRuleRepository
     */
    public function rule(): IRuleRepository
    {
        return $this->rule_repository;
    }

    /**
     * @return IConfirmationRepository
     */
    public function confirmation(): IConfirmationRepository
    {
        return $this->confirmation_repository;
    }

    /**
     * @return IReminderRepository
     */
    public function reminder(): IReminderRepository
    {
        return $this->reminder_repository;
    }

    /**
     * @return IWhitelistRepository
     */
    public function whitelist(): IWhitelistRepository
    {
        return $this->whitelist_repository;
    }

    public function token(): ITokenRepository
    {
        return $this->token_repository;
    }
}
