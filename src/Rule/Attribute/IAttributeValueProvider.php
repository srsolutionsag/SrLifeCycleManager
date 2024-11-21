<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IAttributeValueProvider
{
    /**
     * Must return the attribute type of this factory.
     */
    public function getAttributeType(): string;

    /**
     * Must return a list of attributes prodived by this factory.
     *
     * This method will mostly be used when building forms inputs
     * dynamically.
     *
     * @return string[]
     */
    public function getAttributeValues(): array;
}
