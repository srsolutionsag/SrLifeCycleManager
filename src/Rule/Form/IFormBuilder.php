<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Rule\Form;

use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormBuilder
{
    /**
     * @param string $form_action
     * @return Form
     */
    public function getForm(string $form_action) : Form;
}