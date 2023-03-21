<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ObjectAttribute implements IAttribute
{
    /**
     * @var \ilObject
     */
    private $object;

    public function __construct(\ilObject $object)
    {
        $this->object = $object;
    }

    public function getObject(): \ilObject
    {
        return $this->object;
    }
}
