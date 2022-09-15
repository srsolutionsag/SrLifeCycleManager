<?php

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IEvent
{
    /**
     * @return string
     */
    public function getName() : string;
}
