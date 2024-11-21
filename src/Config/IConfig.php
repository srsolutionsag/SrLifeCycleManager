<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfig
{
    /**
     * @var string config primary key that determines which global roles are allowed to
     *             manage routines (and assignments).
     */
    public const CNF_ROLE_MANAGE_ROUTINES = 'cnf_role_manage_routines';

    /**
     * @var string config primary key that determines which global roles are allowed to
     *             manage assignments.
     */
    public const CNF_ROLE_MANAGE_ASSIGNMENTS = 'cnf_role_manage_assignments';

    /**
     * @var string config primary key that determines if the tool in enabled.
     */
    public const CNF_TOOL_IS_ENABLED = 'cnf_tool_is_enabled';

    /**
     * @var string config primary key that determines if the tool shows active routines.
     */
    public const CNF_TOOL_SHOW_ROUTINES = 'cnf_tool_show_routines';

    /**
     * @var string config primary key that determines if the tool shows controls (for
     *             configured roles).
     */
    public const CNF_TOOL_SHOW_CONTROLS = 'cnf_tool_show_controls';

    /**
     * @var string config primary key to define a custom email-address from which the
     *             ilSrNotificationSender will send notifications.
     */
    public const CNF_CUSTOM_FROM_EMAIL = 'cnf_custom_from_email';

    /**
     * @var string config primary key to define a set of users that will be ignored when
     *             sending notifications. This is necessary due to system-users like Cron.
     */
    public const CNF_MAILING_BLACKLIST = 'cnf_mailing_blacklist';

    /**
     * @var string config primary key to force mail-forwarding even though the user might
     *             have disabled it.
     */
    public const CNF_FORCE_MAIL_FORWARDING = 'cnf_force_mail_forwarding';

    /**
     * @var string config primary key to enable or disable the debugging of this plugin.
     */
    public const CNF_DEBUG_MODE = 'cnf_debug_mode';

    // IConfig attribute names:
    public const F_IDENTIFIER = 'identifier';
    public const F_CONFIG = 'configuration';

    /**
     * @return int[]
     */
    public function getManageRoutineRoles(): array;

    /**
     * @param int[] $roles
     * @return IConfig
     */
    public function setManageRoutineRoles(array $roles): IConfig;

    /**
     * @return int[]
     */
    public function getManageAssignmentRoles(): array;

    /**
     * @param int[] $roles
     * @return IConfig
     */
    public function setManageAssignmentRoles(array $roles): IConfig;

    /**
     * @return bool
     */
    public function isToolEnabled(): bool;

    /**
     * @param bool $is_enabled
     * @return IConfig
     */
    public function setToolEnabled(bool $is_enabled): IConfig;

    /**
     * @return bool
     */
    public function shouldToolShowRoutines(): bool;

    /**
     * @param bool $should_show
     * @return IConfig
     */
    public function setShouldToolShowRoutines(bool $should_show): IConfig;

    /**
     * @return bool
     */
    public function shouldToolShowControls(): bool;

    /**
     * @param bool $should_show
     * @return IConfig
     */
    public function setShouldToolShowControls(bool $should_show): IConfig;

    /**
     * @return string|null
     */
    public function getNotificationSenderAddress(): ?string;

    /**
     * @param string $email
     * @return IConfig
     */
    public function setNotificationSenderAddress(string $email): IConfig;

    /**
     * @return bool
     */
    public function isMailForwardingForced(): bool;

    /**
     * @param bool $is_forced
     * @return IConfig
     */
    public function setMailForwardingForced(bool $is_forced): IConfig;

    /**
     * @return int[]
     */
    public function getMailingBlacklist(): array;

    /**
     * @param int[] $user_ids
     * @return IConfig
     */
    public function setMailingBlacklist(array $user_ids): IConfig;

    /**
     * @return bool
     */
    public function isDebugModeEnabled(): bool;

    /**
     * @var bool $is_enabled
     * @return IConfig
     */
    public function setDebugModeEnabled(bool $is_enabled): IConfig;
}
