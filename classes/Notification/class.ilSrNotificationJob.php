<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationJob extends ilSrAbstractCronJob
{
    /**
     * @var ilMailMimeSender
     */
    protected $mail_sender;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilLogger                       $logger
     * @param IConfig                        $config
     * @param ilMailMimeSender               $mail_sender
     * @param ilCtrl                         $ctrl
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilLogger $logger,
        IConfig $config,
        ilMailMimeSender $mail_sender,
        ilCtrl $ctrl
    ) {
        parent::__construct($repository, $logger, $config);

        $this->mail_sender = $mail_sender;
        $this->ctrl = $ctrl;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Notifications';
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
    public function run() : ilCronJobResult
    {
        $starting_time = microtime(true);

        foreach ($this->repository->routine()->getAll() as $routine) {
            $exec_date = $this->repository->routine()->getNextExecutionDate($routine);
            if (null === $exec_date) {
                continue;
            }

            $notifications = $this->repository->notification()->getAllByRoutineExecutionDate($exec_date);
            foreach ($notifications as $notification) {
                $objects = $this->repository->getDeletableObjects($routine->getRefId());
                foreach ($objects as $object) {
                    $object_ref_id = (int) $object['ref_id'];
                    $object_administrators = $this->repository->getAdministrators($object_ref_id);
                    foreach ($object_administrators as $admin_id) {
                        if (ilObjUser::_exists($admin_id)) {
                            $this->sendNotification($notification, new ilObjUser($admin_id), $object_ref_id);
                        }
                    }
                }
            }
        }

        $ending_time = microtime(true);

        $result = new ilCronJobResult();
        $result->setDuration(($starting_time - $ending_time));
        $result->setMessage($this->getId() . " terminated successfully.");
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }

    /**
     * @param IRoutineAwareNotification $notification
     * @param ilObjUser                 $user
     * @param int                       $ref_id
     * @return void
     */
    protected function sendNotification(
        IRoutineAwareNotification $notification,
        ilObjUser $user,
        int $ref_id
    ) : void {
        $message = $notification->getMessage();
        $message = str_replace(
            [
                '[OBJECT_LINK]',
                '[EXTENSION_LINK]',
            ],
            [
                ilLink::_getLink($ref_id),
                $this->getExtensionLink($notification->getRoutineId(), $ref_id),
            ],
            $message
        );

        $mail = new ilMimeMail();
        $mail->From($this->mail_sender);
        $mail->To($user->getEmail());
        $mail->Subject('Reminder');
        $mail->Body($message);
        $mail->Send();
    }

    /**
     * Returns a temporary  (due to ilCtrl CIDs) link target to delay the
     * deletion of the object for the given ref-id.
     *
     * @param int $routine_id
     * @param int $ref_id
     * @return string
     */
    protected function getExtensionLink(int $routine_id, int $ref_id) : string
    {
        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::QUERY_PARAM_ROUTINE_ID,
            $routine_id
        );

        $this->ctrl->setParameterByClass(
            ilSrRoutineGUI::class,
            'ref_id',
            $ref_id
        );

        return ilSrLifeCycleManagerDispatcher::buildFullyQualifiedLinkTarget(
            ilSrRoutineGUI::class,
            ilSrRoutineGUI::CMD_WHITELIST_ADD
        );
    }
}