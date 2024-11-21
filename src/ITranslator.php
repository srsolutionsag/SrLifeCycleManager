<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

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
    public function txt(string $lang_var): string;
}
