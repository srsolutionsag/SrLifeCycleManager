<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Observer
{
    /**
     * @var self|null
     */
    protected static $instance = null;

    /**
     * @var IEventListener[]
     */
    protected $event_listeners = [];

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register(IEventListener $listener): self
    {
        $this->event_listeners[$listener->getId()] = $listener;
        return $this;
    }

    public function broadcast(IEvent $event): self
    {
        foreach ($this->event_listeners as $listener) {
            $listener->handle($event);
        }

        return $this;
    }

    protected function __construct()
    {
    }

    protected function __wakeup()
    {
    }

    protected function __clone()
    {
    }
}
