<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Routine;

use srag\Plugins\SrLifeCycleManager\Event\NamedEvent;
use ilObject;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineEvent extends NamedEvent
{
    // RoutineEvent actions:
    public const POSTPONE = 'onPostpone';
    public const OPT_OUT = 'onOptOut';
    public const DELETE = 'onDelete';

    /**
     * @var string possible event-actions.
     */
    public const ACTIONS = [
        self::POSTPONE,
        self::OPT_OUT,
        self::DELETE,
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
     * @param string   $source
     * @param string   $name
     */
    public function __construct(
        IRoutine $routine,
        ilObject $object,
        string $source,
        string $name
    ) {
        parent::__construct($source, $name);
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
     * Returns whether the current event-action is repeatable.
     *
     * @return bool
     */
    public function isRepeatable() : bool
    {
        return ($this->getAction() === self::POSTPONE);
    }
}
