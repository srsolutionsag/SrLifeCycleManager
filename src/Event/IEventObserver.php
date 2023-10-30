<?php

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IEventObserver
{
    /**
     * Must return a unique identifier for this observer.
     */
    public function getId(): string;

    /**
     * Should handle the event it has been attached to and perform
     * the desired tasks.
     *
     * @param mixed|null $data
     */
    public function update(string $event, $data = null): void;
}