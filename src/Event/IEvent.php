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
    public function getAction() : string;

    /**
     * @return string
     */
    public function getSource() : string;
}
