<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * IForm is a wrapper class for ILIAS\UI\Component\Input\Container\Form\Form.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormBuilder
{
    /**
     * @return UIForm
     */
    public function getForm() : UIForm;
}