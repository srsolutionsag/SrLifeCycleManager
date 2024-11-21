<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ObjectAttribute implements IAttribute
{
    private \ilObject $object;

    public function __construct(\ilObject $object)
    {
        $this->object = $object;
    }

    public function getObject(): \ilObject
    {
        return $this->object;
    }
}
