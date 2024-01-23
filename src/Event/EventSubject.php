<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class EventSubject
{
    /**
     * @var string string to represent all events.
     */
    public const ALL_EVENTS = 'all';

    /**
     * @var array<string, IEventObserver[]>
     */
    protected $observer_groups = [];

    /**
     * Attach an observer to a specific event, defaults to ALL_EVENTS.
     *
     * Please note that ALL_EVENTS is an event on its own, and an observer will
     * be attached to this event as it would be to any other event.
     */
    public function attach(IEventObserver $observer, string $event = self::ALL_EVENTS): void
    {
        $this->initObserverGroup($event);

        $this->observer_groups[$event][] = $observer;
    }

    /**
     * Detaches an observer from a specific event, defaults to all events.
     *
     * Please note that ALL_EVENTS is an event on its own, detatching from it
     * will therefore not detach from any ohter events.
     */
    public function detach(IEventObserver $observer, string $event = self::ALL_EVENTS): void
    {
        $this->initObserverGroup($event);

        foreach ($this->observer_groups[$event] as $index => $attached_observer) {
            if ($attached_observer->getId() === $observer->getId()) {
                unset($this->observer_groups[$event][$index]);
            }
        }
    }

    /**
     * Notify all observers about a specific event and provides some data.
     *
     * @param mixed|null $data
     */
    public function notify(string $event, $data = null): void
    {
        $this->initObserverGroup($event);

        $observers = array_merge(
            $this->observer_groups[self::ALL_EVENTS],
            $this->observer_groups[$event],
        );

        foreach ($observers as $interessted_observer) {
            $interessted_observer->update($event, $data);
        }
    }

    protected function initObserverGroup(string $group): void
    {
        if (!isset($this->observer_groups[$group])) {
            $this->observer_groups[$group] = [];
        }
    }
}
