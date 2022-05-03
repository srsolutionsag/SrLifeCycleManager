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
        $message = $this->getNotificationBody($object, $notification);
        $subject = $notification->getTitle();

        foreach ($administrators as $user_id) {
            // it's possible that participants are delivered whose user
            // accounts were deleted.
            if (!ilObjUser::_exists($user_id)) {
                continue;
            }

            $recipient = new ilObjUser($user_id);

            $this->sendIliasMail($recipient, $subject, $message);
            $this->sendMimeMail($recipient, $subject, $message);
        }

        return $this->repository->notifyObject($notification, $object->getRefId());
    }

    /**
     * Sends an actual email to the given recipient with the given subject and message.
     *
     * @param ilObjUser $recipient
     * @param string    $subject
     * @param string    $message
     */
    protected function sendMimeMail(ilObjUser $recipient, string $subject, string $message) : void
    {
        $mail = new ilMimeMail();
        $mail->From($this->mail_sender);
        $mail->To($recipient->getEmail());
        $mail->Subject($subject);
        $mail->Body($message);
        $mail->Send();
    }

    /**
     * Sends an ILIAS notification to the given recipient with the given subject and message.
     *
     * @param ilObjUser $recipient
     * @param string    $subject
     * @param string    $message
     */
    protected function sendIliasMail(ilObjUser $recipient, string $subject, string $message) : void
    {
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->setSaveInSentbox(true);
        $mail->enqueue(
            $recipient->getLogin(),
            '',
            '',
            $subject,
            $message,
            []
        );
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
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_ROUTINE_ID,
            $notification->getRoutineId()
        );

        $this->ctrl->setParameterByClass(
            ilSrWhitelistGUI::class,
            ilSrWhitelistGUI::PARAM_OBJECT_REF_ID,
            $object->getRefId()
        );

        return str_replace(
            [
                '[OBJECT_TITLE]',
                '[OBJECT_LINK]',
                '[EXTENSION_LINK]',
                '[OPT_OUT_LINK]',
            ],
            [
                $object->getTitle(),
                ilLink::_getStaticLink($object->getRefId()),
                ILIAS_HTTP_PATH . '/' . ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_POSTPONE
                ),
                ILIAS_HTTP_PATH . '/' . ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrWhitelistGUI::class,
                    ilSrWhitelistGUI::CMD_WHITELIST_OPT_OUT
                ),
            ],
            $notification->getContent()
        );
    }
}