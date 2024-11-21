<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Object;

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ilObject;

/**
 * This object is used to represent a 1:1 relationship between an object and a
 * routine. This relationship can be interpreted in different ways.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AffectedObject
{
    public function __construct(protected \ilObject $object, protected IRoutine $routine)
    {
    }

    public function getObject(): ilObject
    {
        return $this->object;
    }

    public function getRoutine(): IRoutine
    {
        return $this->routine;
    }
}
