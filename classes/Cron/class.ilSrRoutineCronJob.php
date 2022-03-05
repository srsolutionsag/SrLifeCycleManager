<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObjectGenerator;
use srag\Plugins\SrLifeCycleManager\Rule\Generator\IDeletableObject;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\IRepository;
use srag\Plugins\SrLifeCycleManager\ITranslator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineCronJob extends ilSrAbstractCronJob
{
    /**
     * @var IDeletableObjectGenerator|IDeletableObject[]
     */
    protected $deletable_objects;

    /**
     * @var ilMailMimeSender
     */
    protected $mail_sender;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilSrResultBuilder         $builder
     * @param ilMailMimeSender          $mail_sender
     * @param IDeletableObjectGenerator $generator
     * @param IRepository               $repository
     * @param ITranslator               $translator
     * @param ilLogger                  $logger
     * @param ilCtrl                    $ctrl
     */
    public function __construct(
        ilSrResultBuilder $builder,
        ilMailMimeSender $mail_sender,
        IDeletableObjectGenerator $generator,
        IRepository $repository,
        ITranslator $translator,
        ilLogger $logger,
        ilCtrl $ctrl
    ) {
        parent::__construct($builder, $repository, $translator, $logger);

        $this->deletable_objects = $generator;
        $this->mail_sender = $mail_sender;
        $this->ctrl = $ctrl;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routine Job';
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '...';
    }

    /**
     * @inheritDoc
     */
    protected function execute() : void
    {
        $this->result_builder->request();

        foreach ($this->deletable_objects as $object) {
            $object_instance = $object->getInstance();
            $object_ref_id = $object_instance->getRefId();

            foreach ($object->getAffectedRoutines() as $routine) {
                $notifications = $this->repository->notification()->getByRoutine($routine);
                $whitelist_entry = $this->repository->routine()->whitelist()->get($routine, $object_ref_id);

                // if no notifications are registered and the object is
                // not whitelisted, it can be deleted immediately.
                if (empty($notifications) &&
                    null === $whitelist_entry
                ) {
                    $this->deleteObject($object_instance);
                    break;
                }

                $sent_notifications = $this->repository->notification()->getSentNotifications($routine, $object_ref_id);

                $all_notifications_sent = (count($notifications) === count($sent_notifications));
                $is_whitelisted = (null !== $whitelist_entry);
                $is_opted_out = ($is_whitelisted && $whitelist_entry->isOptOut());
                $is_extended = ($is_whitelisted && !$is_opted_out && null !== $whitelist_entry->getElongation());
                $is_elapsed = (
                    $is_extended &&
                    (new DateTime) > $whitelist_entry->getDate()->add(
                        new DateInterval("P{$whitelist_entry->getElongation()}D")
                    )
                );

                // if all notifications were sent, the object can be deleted if
                //      (a) the object is not whitelisted, or
                //      (b) the object was an extension that has been elapsed.
                if ($all_notifications_sent &&
                    (!$is_whitelisted || (!$is_opted_out && $is_extended && $is_elapsed))
                ) {
                    $this->deleteObject($object_instance);
                    break;
                }

                $next_notification = $notifications[count($sent_notifications) - 1];
                $this->notifyObject($object_instance, $next_notification);
            }
        }
    }

    /**
     * @param ilObject      $object
     * @param INotification $notification
     * @return void
     */
    protected function notifyObject(ilObject $object, INotification $notification) : void
    {
        $administrators = ilParticipants::getInstance($object->getRefId())->getAdmins();
        foreach ($administrators as $user_id) {
            if (!ilObjUser::_exists($user_id)) {
                $this->error("Object ({$object->getRefId()}) administrator '$user_id' does not exist anymore.");
                continue;
            }

            $user = new ilObjUser($user_id);
            $mail = new ilMimeMail();

            try {
                $this->info("Notifying object ({$object->getRefId()}) administrator '{$user->getEmail()}'.");
                $mail->From($this->mail_sender);
                $mail->To($user->getEmail());
                $mail->Subject($notification->getTitle());
                $mail->Body($this->getNotificationBody($object, $notification));
                $mail->Send();
            } catch (Throwable $throwable) {
                $this->error("Could not notify object ({$object->getRefId()}) administrator '{$user->getEmail()}': {$throwable->getMessage()}");
                continue;
            }
        }
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
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_EXTEND
                ),
                ilSrLifeCycleManagerDispatcher::getLinkTarget(
                    ilSrRoutineGUI::class,
                    ilSrRoutineGUI::CMD_ROUTINE_OPT_OUT
                ),
            ],
            $notification->getContent()
        );
    }

    /**
     * Tries to delete the given object, if it fails a corresponding
     * log entry will be made.
     *
     * @param ilObject $object
     * @return void
     */
    protected function deleteObject(ilObject $object) : void
    {
        $this->info("Deleting object {$object->getRefId()} ({$object->getType()})");
        try {
            ilRepUtil::deleteObjects(null, $object->getRefId());
        } catch (ilRepositoryException $exception) {
            $this->error("Could not delete object {$object->getRefId()}: {$exception->getMessage()}");
        }
    }
}