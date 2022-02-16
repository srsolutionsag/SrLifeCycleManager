<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Renderer;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Notification\NotificationFormBuilder;
use srag\Plugins\SrLifeCycleManager\Notification\Notification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrNotificationForm extends ilSrAbstractForm
{
    /**
     * @var INotification|null
     */
    protected $notification;

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilGlobalTemplateInterface      $global_template
     * @param Renderer                       $renderer
     * @param Form                           $form
     * @param IRoutine                       $routine
     * @param INotification|null             $notification
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilGlobalTemplateInterface $global_template,
        Renderer $renderer,
        Form $form,
        IRoutine  $routine,
        INotification $notification = null
    ) {
        parent::__construct($repository, $global_template, $renderer, $form);

        $this->notification = $notification;
        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // the submitted notification is invalid if the
        // message is empty.
        return empty($form_data[NotificationFormBuilder::INPUT_NOTIFICATION_MESSAGE]);
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        $notification_message = $form_data[NotificationFormBuilder::INPUT_NOTIFICATION_MESSAGE];

        if (null === $this->notification) {
            $notification = $this->repository->notification()->store(
                new Notification(
                    null,
                    $notification_message
                )
            );

            $this->repository->routine()->addNotification($this->routine, $notification);
        } else {
            $this->repository->notification()->store(
                $this->notification->setMessage($notification_message)
            );
        }
    }
}