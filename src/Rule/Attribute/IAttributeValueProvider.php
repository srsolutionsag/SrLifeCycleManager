<?php

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
