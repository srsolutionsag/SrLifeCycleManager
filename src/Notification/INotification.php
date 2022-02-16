<?php

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotification
{
    /**
     * INotification attribute names
     */
    public const F_ID       = 'id';
    public const F_MESSAGE  = 'message';

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