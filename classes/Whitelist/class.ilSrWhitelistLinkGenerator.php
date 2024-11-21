<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineEvent;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistLinkGenerator
{
    /**
     * @var ITokenRepository
     */
    protected $token_repository;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ITokenRepository $token_repository
     * @param ilCtrl           $ctrl
     */
    public function __construct(ITokenRepository $token_repository, ilCtrl $ctrl)
    {
        $this->token_repository = $token_repository;
        $this->ctrl = $ctrl;
    }

    /**
     * Returns an absolute URL to the whitelist GUI that holds an according
     * whitelist token for elongations.
     *
     * @param int $routine_id
     * @param int $ref_id
     * @return string
     */
    public function getElongationLink(int $routine_id, int $ref_id): string
    {
        $token =
            $this->token_repository->get($routine_id, $ref_id, IRoutineEvent::EVENT_POSTPONE) ??
            $this->token_repository->new($routine_id, $ref_id, IRoutineEvent::EVENT_POSTPONE)
        ;

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_WHITELIST_TOKEN,
            $token->getToken()
        );

        return ILIAS_HTTP_PATH . '/' . ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::CMD_WHITELIST_POSTPONE
        );
    }

    /**
     * Returns an absolute URL to the whitelist GUI that holds an according
     * whitelist token for opt-outs.
     *
     * @param int $routine_id
     * @param int $ref_id
     * @return string
     */
    public function getOptOutLink(int $routine_id, int $ref_id): string
    {
        $token =
            $this->token_repository->get($routine_id, $ref_id, IRoutineEvent::EVENT_OPT_OUT) ??
            $this->token_repository->new($routine_id, $ref_id, IRoutineEvent::EVENT_OPT_OUT)
        ;

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_WHITELIST_TOKEN,
            $token->getToken()
        );

        return ILIAS_HTTP_PATH . '/' . ilSrLifeCycleManagerDispatcherGUI::getLinkTarget(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT
        );
    }
}
