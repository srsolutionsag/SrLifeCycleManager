<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ITranslator
{
    /**
     * @param string $lang_var
     * @return string
     */
    public function txt(string $lang_var) : string;
}