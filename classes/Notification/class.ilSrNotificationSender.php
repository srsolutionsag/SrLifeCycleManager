<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationSender;
use srag\Plugins\SrLifeCycleManager\Notification\ISentNotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * This class is responsible for sending notifications to object
 * administrators.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
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
    protected $notification_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var ilSrWhitelistLinkGenerator
     */
    protected $whitelist_link_generator;

    /**
     * @var ilMailMimeSender
     */
    protected $mail_sender;

    /**
     * @var IConfig
     */
    protected $config;

    public function __construct(
        INotificationRepository $notification_repository,
        IRoutineRepository $routine_repository,
        ilSrWhitelistLinkGenerator $whitelist_link_generator,
        ilMailMimeSender $mail_sender,
        IConfig $config
    ) {
        $this->notification_repository = $notification_repository;
        $this->routine_repository = $routine_repository;
        $this->whitelist_link_generator = $whitelist_link_generator;
        $this->mail_sender = $mail_sender;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function sendNotification(
        IRecipientRetriever $recipient_retriever,
        INotification $notification,
        ilObject $object
    ): ISentNotification {
        // it's important the message is parsed outside the loop, otherwise
        // the whitelist-tokens will be overwritten each iteration.
        $message = $this->getNotificationBody($object, $notification);

        // HS_Brhv_Sascha: allow object title in notification subject
        $subject = str_replace('[OBJECT_TITLE]', $object->getTitle(), $notification->getTitle());

        foreach ($recipient_retriever->getRecipients($object) as $user_id) {
            // only send notifications to users that still exist and are not contained
            // on the configured blacklist.
            if (ilObjUser::_exists($user_id) &&
                !in_array($user_id, $this->config->getMailingBlacklist(), true)
            ) {
                $this->sendNotificationToUser(new ilObjUser($user_id), $subject, $message);
            }
        }

        return $this->notification_repository->markObjectAsNotified($notification, $object->getRefId());
    }

    /**
     * Replaces the notifications placeholders with their supposed values.
     *
     * @TODO: this method could be replaced by some more general "blocks"
     *        service (name in progress).
     *
     * @param ilObject      $object
     * @param INotification $notification
     * @return string
     */
    protected function getNotificationBody(ilObject $object, INotification $notification): string
    {
        $message = $notification->getContent();
        $routine = null;

        if (strpos($message, '[EXTENSION_LINK]')) {
            $routine = $this->routine_repository->get($notification->getRoutineId());

            // link defaults to 'unavailable' if the routine doesn't support elongations.
            $link = (null !== $routine && 0 < $routine->getElongation()) ?
                $this->whitelist_link_generator->getElongationLink(
                    $notification->getRoutineId(),
                    $object->getRefId()
                ) : 'unavailable';

            $message = str_replace('[EXTENSION_LINK]', $link, $message);
        }

        if (strpos($message, '[OPT_OUT_LINK]')) {
            // use the previously fetched routine if possible.
            $routine = $routine ?? $this->routine_repository->get($notification->getRoutineId());

            // link defaults to 'unavailable' if the routine doesn't support opt-outs.
            $link = (null !== $routine && $routine->hasOptOut()) ?
                $this->whitelist_link_generator->getOptOutLink(
                    $notification->getRoutineId(),
                    $object->getRefId()
                ) : 'unavailable';

            $message = str_replace('[OPT_OUT_LINK]', $link, $message);
        }

        return str_replace([
            '[OBJECT_LINK]',
            '[OBJECT_TITLE]',
        ], [
            ilLink::_getStaticLink($object->getRefId()),
            $object->getTitle(),
        ], $message);
    }

    /**
     * @param ilObjUser $recipient
     * @param string    $subject
     * @param string    $message
     * @return void
     */
    protected function sendNotificationToUser(ilObjUser $recipient, string $subject, string $message): void
    {
        $this->sendIliasMail($recipient, $subject, $message);

        if ($this->config->isMailForwardingForced() &&
            !$this->hasUserEnabledForwarding($recipient)
        ) {
            $this->sendMimeMail($recipient, $subject, $message);
        }
    }

    /**
     * Sends an actual email to the given recipient with the given subject and message.
     *
     * @param ilObjUser $recipient
     * @param string    $subject
     * @param string    $message
     */
    protected function sendMimeMail(ilObjUser $recipient, string $subject, string $message): void
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
    protected function sendIliasMail(ilObjUser $recipient, string $subject, string $message): void
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
     * @param ilObject $user
     * @return bool
     */
    protected function hasUserEnabledForwarding(ilObject $user): bool
    {
        return (ilMailOptions::INCOMING_LOCAL !== ((new ilMailOptions($user->getId()))->getIncomingType()));
    }
}
