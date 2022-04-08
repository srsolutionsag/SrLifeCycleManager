<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrWhitelistTable extends ilSrAbstractTable
{
    // ilSrWhitelistTable column names:
    protected const COL_REF_ID = 'col_whitelist_ref_id';
    protected const COL_USER_NAME = 'col_whitelist_user_name';
    protected const COL_IS_OPT_OUT = 'col_whitelist_opt_out';
    protected const COL_ELONGATION = 'col_whitelist_elongation';
    protected const COL_DATE = 'col_whitelist_date';

    // ilSrWhitelistTable language variables:
    protected const STATUS_IS_OPT_OUT = 'status_is_opt_out';
    protected const STATUS_IS_NOT_OPT_OUT = 'status_is_not_opt_out';
    protected const ACTION_VIEW_OBJECT = 'action_view_object';

    /**
     * @inheritDoc
     */
    protected function getTemplateName() : string
    {
        return 'tpl.whitelist_table_row.html';
    }

    /**
     * @inheritDoc
     */
    protected function addTableColumns() : void
    {
        $this->addColumn($this->translator->txt(self::COL_REF_ID));
        $this->addColumn($this->translator->txt(self::COL_USER_NAME));
        $this->addColumn($this->translator->txt(self::COL_IS_OPT_OUT));
        $this->addColumn($this->translator->txt(self::COL_ELONGATION));
        $this->addColumn($this->translator->txt(self::COL_DATE));
        $this->addActionColumn();
    }

    /**
     * @inheritDoc
     */
    protected function renderTableRow(ilTemplate $template, array $data) : void
    {
        // if the 'usr_id' still exists, get the login-name.
        $user_name = (ilObjUser::_exists($data[IWhitelistEntry::F_USER_ID])) ?
            (new ilObjUser((int) $data[IWhitelistEntry::F_USER_ID]))->getLogin() : ''
        ;

        // translate the status of 'opt_out_possible'.
        $status_opt_out = ($data[IWhitelistEntry::F_IS_OPT_OUT]) ?
            $this->translator->txt(self::STATUS_IS_OPT_OUT) :
            $this->translator->txt(self::STATUS_IS_NOT_OPT_OUT)
        ;

        $template->setVariable(self::COL_REF_ID, $data[IWhitelistEntry::F_REF_ID]);
        $template->setVariable(self::COL_USER_NAME, $user_name);
        $template->setVariable(self::COL_IS_OPT_OUT, $status_opt_out);
        $template->setVariable(self::COL_ELONGATION, $data[IWhitelistEntry::F_ELONGATION]);
        $template->setVariable(self::COL_DATE, $data[IWhitelistEntry::F_DATE]);

        $template->setVariable(
            self::COL_ACTIONS,
            $this->renderer->render(
                $this->getActionDropdown((int) $data[IWhitelistEntry::F_REF_ID])
            )
        );
    }

    /**
     * @param int $ref_id
     * @return Dropdown
     */
    protected function getActionDropdown(int $ref_id) : Dropdown
    {
        return $this->ui_factory->dropdown()->standard([
            $this->ui_factory->button()->shy(
                $this->translator->txt(self::ACTION_VIEW_OBJECT),
                ilLink::_getLink($ref_id)
            )
        ]);
    }
}