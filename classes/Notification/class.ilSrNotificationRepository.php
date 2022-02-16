<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class ilSrNotificationRepository implements INotificationRepository
{
    /**
     * @inheritDoc
     */
    public function get(int $notification_id) : ?INotification
    {
        /**
         * @var $ar_notification ilSrNotification|null
         */
        $ar_notification = ilSrNotification::find($notification_id);
        if (null !== $ar_notification) {
            return $this->transformToDTO($ar_notification);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function store(INotification $notification) : INotification
    {
        $ar_notification = (null !== $notification->getId()) ?
            (ilSrNotification::find($notification->getId()) ?? new ilSrNotification()) :
            new ilSrNotification()
        ;

        $ar_notification->setMessage($notification->getMessage());
        $ar_notification->store();

        return $this->transformToDTO($ar_notification);
    }

    /**
     * @inheritDoc
     */
    public function delete(INotification $notification) : bool
    {
        // there's nothing to do if the DTO hasn't been stored yet
        if (null === $notification->getId()) return true;

        $ar_notification = ilSrNotification::find($notification->getId());
        if (null !== $ar_notification) {
            $ar_notification->delete();
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function transformToDTO(INotification $ar_notification) : INotification
    {
        return new Notification(
            $ar_notification->getId(),
            $ar_notification->getMessage()
        );
    }

    /**
     * @inheritDoc
     */
    public function transformToArray(INotification $ar_notification) : array
    {
        return [
            INotification::F_ID => $ar_notification->getId(),
            INotification::F_MESSAGE => $ar_notification->getMessage()
        ];
    }
}