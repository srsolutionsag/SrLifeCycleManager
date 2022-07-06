<?php

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IEventListener
{
    /**
     * @param IEvent $event
     */
    public function handle(IEvent $event) : void;
}
