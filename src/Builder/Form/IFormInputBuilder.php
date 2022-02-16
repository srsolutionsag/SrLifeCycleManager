<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form;

use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormInputBuilder
{
    /**
     * @return string
     */
    public function getInputName() : string;

    /**
     * @return Input
     */
    public function getInput() : Input;
}