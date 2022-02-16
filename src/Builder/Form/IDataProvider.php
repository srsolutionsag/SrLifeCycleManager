<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IDataProvider
{
    /**
     * @return mixed
     */
    public function getInputValue();
}