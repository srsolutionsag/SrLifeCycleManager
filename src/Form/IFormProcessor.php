<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormProcessor
{
    /**
     * @return bool
     */
    public function processForm() : bool;

    /**
     * @return UIForm
     */
    public function getProcessedForm() : UIForm;
}