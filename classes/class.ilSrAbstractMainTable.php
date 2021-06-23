<?php

abstract class ilSrAbstractMainTable extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
    }
}