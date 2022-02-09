<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * Interface IRoutineNotification defines how a routine-rule relation must look like.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotification
{
    /**
     * @return int|null
     */
    public function getId() : ?int;

    /**
     * @param int $id
     * @return INotification
     */
    public function setId(int $id) : INotification;

    /**
     * @return string
     */
    public function getMessage() : string;

    /**
     * @param string $message
     * @return INotification
     */
    public function setMessage(string $message) : INotification;
}