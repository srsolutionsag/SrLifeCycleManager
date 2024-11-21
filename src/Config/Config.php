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
class Config implements IConfig
{
    /**
     * @param int[] $manage_routine_roles
     * @param int[] $manage_assignment_roles
     * @param bool $tool_is_enabled
     * @param bool $tool_show_routines
     * @param bool $tool_show_controls
     * @param string|null $custom_email
     * @param array $mailing_blacklist
     * @param bool $force_mail_forwarding
     * @param bool $is_debug_mode_enabled
     */
    public function __construct(
        protected array $manage_routine_roles = [],
        protected array $manage_assignment_roles = [],
        protected bool $tool_is_enabled = false,
        protected bool $tool_show_routines = false,
        protected bool $tool_show_controls = false,
        protected ?string $custom_email = null,
        protected array $mailing_blacklist = [],
        protected bool $force_mail_forwarding = false,
        protected bool $is_debug_mode_enabled = false
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getManageRoutineRoles(): array
    {
        return $this->manage_routine_roles;
    }

    /**
     * @inheritDoc
     */
    public function setManageRoutineRoles(array $roles): IConfig
    {
        $this->manage_routine_roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getManageAssignmentRoles(): array
    {
        return $this->manage_assignment_roles;
    }

    /**
     * @inheritDoc
     */
    public function setManageAssignmentRoles(array $roles): IConfig
    {
        $this->manage_assignment_roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isToolEnabled(): bool
    {
        return $this->tool_is_enabled;
    }

    /**
     * @inheritDoc
     */
    public function setToolEnabled(bool $is_enabled): IConfig
    {
        $this->tool_is_enabled = $is_enabled;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shouldToolShowRoutines(): bool
    {
        return $this->tool_show_routines;
    }

    /**
     * @inheritDoc
     */
    public function setShouldToolShowRoutines(bool $should_show): IConfig
    {
        $this->tool_show_routines = $should_show;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shouldToolShowControls(): bool
    {
        return $this->tool_show_controls;
    }

    /**
     * @inheritDoc
     */
    public function setShouldToolShowControls(bool $should_show): IConfig
    {
        $this->tool_show_controls = $should_show;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationSenderAddress(): ?string
    {
        return $this->custom_email;
    }

    /**
     * @inheritDoc
     */
    public function setNotificationSenderAddress(string $email): IConfig
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
    public function getMailingBlacklist(): array
    {
        return $this->mailing_blacklist;
    }

    /**
     * @inheritDoc
     */
    public function setMailingBlacklist(array $user_ids): IConfig
    {
        $this->mailing_blacklist = $user_ids;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isDebugModeEnabled(): bool
    {
        return $this->is_debug_mode_enabled;
    }

    /**
     * @inheritDoc
     */
    public function setDebugModeEnabled(bool $is_enabled): IConfig
    {
        $this->is_debug_mode_enabled = $is_enabled;
        return $this;
    }
}
