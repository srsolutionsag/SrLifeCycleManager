<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * Class Notification (DTO)
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class Notification implements INotification
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $message;

    /**
     * Notification constructor
     *
     * @param int|null $id
     * @param string   $message
     */
    public function __construct(
        ?int $id,
        string $message
    ) {
        $this->id       = $id;
        $this->message  = $message;
    }

    /**
     * @ineritdoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @ineritdoc
     */
    public function setId(int $id) : INotification
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @ineritdoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @ineritdoc
     */
    public function setMessage(string $message) : INotification
    {
        $this->message = $message;
        return $this;
    }
}