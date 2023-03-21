<?php

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IEventListener
{
    /**
     * Must return the event listeners unique identification.
     */
    public function getId(): string;

    /**
     * @param IEvent $event
     */
    public function handle(IEvent $event): void;
}
