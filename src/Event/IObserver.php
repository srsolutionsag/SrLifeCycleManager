<?php

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IObserver
{
    /**
     * @param IEventListener $listener
     * @return IObserver
     */
    public function register(IEventListener $listener) : IObserver;

    /**
     * @param IEvent $event
     * @return IObserver
     */
    public function broadcast(IEvent $event) : IObserver;
}
