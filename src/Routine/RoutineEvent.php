<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Event\Event;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineEvent extends Event
{
    // RoutineEvent events:
    public const EVENT_POSTPONE = 'routine_postpone';
    public const EVENT_OPT_OUT = 'routine_opt_out';
    public const EVENT_DELETE = 'routine_delete';

    /**
     * @var string possible event-actions.
     */
    public const EVENT_NAMES = [
        self::EVENT_POSTPONE,
        self::EVENT_OPT_OUT,
        self::EVENT_DELETE,
    ];

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @param IRoutine $routine
     * @param ilObject $object
     * @param string   $name
     */
    public function __construct(
        IRoutine $routine,
        ilObject $object,
        string $name
    ) {
        parent::__construct($name);

        $this->routine = $routine;
        $this->object = $object;
    }

    /**
     * @return IRoutine
     */
    public function getRoutine() : IRoutine
    {
        return $this->routine;
    }

    /**
     * @return ilObject
     */
    public function getObject() : ilObject
    {
        return $this->object;
    }

    /**
     * Returns whether the current event is a repeatable one.
     *
     * @return bool
     */
    public function isRepeatable() : bool
    {
        // since v1.7.0 opt-out's can be undone, therefore the
        // only unrepeatable event is the deletion.
        return (self::EVENT_DELETE !== $this->getName());
    }
}
