<?php /*********************************************************************
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
class Config implements IConfig
{
    /**
     * @var int[] role ids
     */
    protected $manage_routine_roles;

    /**
     * @var int[] role ids
     */
    protected $manage_assignment_roles;

    /**
     * @var bool
     */
    protected $tool_is_enabled;

    /**
     * @var bool
     */
    protected $tool_show_routines;

    /**
     * @var bool
     */
    protected $tool_show_controls;

    /**
     * @var string|null
     */
    protected $custom_email;

    /**
     * @var int[]
     */
    protected $mailing_blacklist;

    /**
     * @var bool
     */
    protected $force_mail_forwarding;

    /**
     * @var bool
     */
    protected $is_debug_mode_enabled;

    /**
     * @param int[]       $manage_routines
     * @param int[]       $manage_assignments
     * @param bool        $is_tool_enabled
     * @param bool        $tool_show_routines
     * @param bool        $tool_show_controls
     * @param string|null $custom_email
     * @param array       $mailing_blacklist
     * @param bool        $force_mail_forwarding
     * @param bool        $is_debug_mode_enabled
     */
    public function __construct(
        array $manage_routines = [],
        array $manage_assignments = [],
        bool $is_tool_enabled = false,
        bool $tool_show_routines = false,
        bool $tool_show_controls = false,
        string $custom_email = null,
        array $mailing_blacklist = [],
        bool $force_mail_forwarding = false,
        bool $is_debug_mode_enabled = false
    ) {
        $this->manage_routine_roles = $manage_routines;
        $this->manage_assignment_roles = $manage_assignments;
        $this->tool_is_enabled = $is_tool_enabled;
        $this->tool_show_routines = $tool_show_routines;
        $this->tool_show_controls = $tool_show_controls;
        $this->custom_email = $custom_email;
        $this->mailing_blacklist = $mailing_blacklist;
        $this->force_mail_forwarding = $force_mail_forwarding;
        $this->is_debug_mode_enabled = $is_debug_mode_enabled;
    }

    /**
     * @inheritDoc
     */
    public function getManageRoutineRoles() : array
    {
        return $this->manage_routine_roles;
    }

    /**
     * @inheritDoc
     */
    public function setManageRoutineRoles(array $roles) : IConfig
    {
        $this->manage_routine_roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getManageAssignmentRoles() : array
    {
        return $this->manage_assignment_roles;
    }

    /**
     * @inheritDoc
     */
    public function setManageAssignmentRoles(array $roles) : IConfig
    {
        $this->manage_assignment_roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isToolEnabled() : bool
    {
        return $this->tool_is_enabled;
    }

    /**
     * @inheritDoc
     */
    public function setToolEnabled(bool $is_enabled) : IConfig
    {
        $this->tool_is_enabled = $is_enabled;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shouldToolShowRoutines() : bool
    {
        return $this->tool_show_routines;
    }

    /**
     * @inheritDoc
     */
    public function setShouldToolShowRoutines(bool $should_show) : IConfig
    {
        $this->tool_show_routines = $should_show;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shouldToolShowControls() : bool
    {
        return $this->tool_show_controls;
    }

    /**
     * @inheritDoc
     */
    public function setShouldToolShowControls(bool $should_show) : IConfig
    {
        $this->tool_show_controls = $should_show;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationSenderAddress() : ?string
    {
        return $this->custom_email;
    }

    /**
     * @inheritDoc
     */
    public function setNotificationSenderAddress(string $email) : IConfig
    {
        $this->custom_email = $email;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isMailForwardingForced(): bool
    {
        return $this->force_mail_forwarding;
    }

    /**
     * @inheritDoc
     */
    public function setMailForwardingForced(bool $is_forced): IConfig
    {
        $this->force_mail_forwarding = $is_forced;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMailingBlacklist() : array
    {
        return $this->mailing_blacklist;
    }

    /**
     * @inheritDoc
     */
    public function setMailingBlacklist(array $user_ids) : IConfig
    {
        $this->mailing_blacklist = $user_ids;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isDebugModeEnabled() : bool
    {
        return $this->is_debug_mode_enabled;
    }

    /**
     * @inheritDoc
     */
    public function setDebugModeEnabled(bool $is_enabled) : IConfig
    {
        $this->is_debug_mode_enabled = $is_enabled;
        return $this;
    }
}