<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface INotification
{
    // INotification attributes:
    public const F_NOTIFICATION_ID = 'notification_id';
    public const F_ROUTINE_ID = 'routine_id';
    public const F_CONTENT = 'content';
    public const F_TITLE = 'title';

    /**
     * @return int|null
     */
    public function getNotificationId(): ?int;

    /**
     * @param int $notification_id
     * @return INotification
     */
    public function setNotificationId(int $notification_id): INotification;

    /**
     * @return int
     */
    public function getRoutineId(): int;

    /**
     * @param int $routine_id
     * @return INotification
     */
    public function setRoutineId(int $routine_id): INotification;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return INotification
     */
    public function setTitle(string $title): INotification;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return INotification
     */
    public function setContent(string $content): INotification;
}
