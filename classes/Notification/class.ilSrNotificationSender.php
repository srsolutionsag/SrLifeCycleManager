<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;

/**
 * This class is responsible for sending notifications to object
 * administrators.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * Whenever notifications must be sent, this class should be used,
 * for it notifies all existing administrators of an object and
 * creates an entry in the database table, to mark the object as
 * notified.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrNotificationSender implements INotificationSender
{
    /**
     * @var INotificationRepository
     */
    protected $repository;

    /**
     * @var ilMailMimeSender
     */
    protected $mail_sender;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param INotificationRepository $repository
     * @param ilMailMimeSender        $mail_sender
     * @param ilCtrl                  $ctrl
     */
    public function __construct(
        INotificationRepository $repository,
        ilMailMimeSender $mail_sender,
        ilCtrl $ctrl
    ) {
        $this->repository = $repository;
        $this->mail_sender = $mail_sender;
        $this->ctrl = $ctrl;
    }

    /**
     * @inheritDoc
     */
    public function sendNotification(INotification $notification, ilObject $object) : ISentNotification
    {
        $administrators = ilParticipants::getInstance($object->getRefId())->getAdmins();
        foreach ($administrators as $user_id) {
            // it's possible that participants are delivered whose user
            // accounts were deleted.
            if (!ilObjUser::_exists($user_id)) {
                continue;
            }

            $user = new ilObjUser($user_id);
            $mail = new ilMimeMail();

            $mail->From($this->mail_sender);
            $mail->To($user->getEmail());
            $mail->Subject($notification->getTitle());
            $mail->Body($this->getNotificationBody($object, $notification));
            $mail->Send();
        }

        return $this->repository->notifyObject($notification, $object->getRefId());
    }

    /**
     * Replaces the notifications placeholders with their supposed values.
     *
     * @param ilObject      $object
     * @param INotification $notification
     * @return string
     */
    protected function getNotificationBody(ilObject $object, INotification $notification) : string
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_ROUTINE_ID,
            $notification->getRoutineId()
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::PARAM_OBJECT_REF_ID,
            $object->getRefId()
        );

        return str_replace(
            [
                '[OBJECT_LINK]',
                '[EXTENSION_LINK]',
                '[EXTENSION_OPT_OUT]',
            ],
            [
                ilLink::_getStaticLink($object->getRefId()),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_ROUTINE_EXTEND
                ),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_ROUTINE_OPT_OUT
                ),
            ],
            $notification->getContent()
        );
    }
}