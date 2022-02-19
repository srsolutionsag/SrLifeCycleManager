<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Form\AbstractForm;
use srag\Plugins\SrLifeCycleManager\IRepository;

use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationForm extends AbstractForm
{
    /**
     * @var IRoutineAwareNotification
     */
    protected $notification;

    /**
     * @param IRepository             $repository
     * @param Renderer                $renderer
     * @param NotificationFormBuilder $builder
     */
    public function __construct(
        IRepository $repository,
        Renderer $renderer,
        NotificationFormBuilder $builder
    ) {
        parent::__construct($repository, $renderer, $builder);

        $this->notification = $builder->getNotification();
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // ensure that the required values are not empty.
        return (
            !empty($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_MESSAGE]) &&
            !empty($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_DAYS])
        );
    }

    /**
     * @inheritDoc
     */
    protected function process(array $post_data) : void
    {
        $days_before_submission = (int) $post_data[NotificationFormBuilder::INPUT_NOTIFICATION_DAYS];
        $notification_message   = $post_data[NotificationFormBuilder::INPUT_NOTIFICATION_MESSAGE];

        $this->notification
            ->setDaysBeforeSubmission($days_before_submission)
            ->setMessage($notification_message)
        ;

        $this->repository->notification()->store($this->notification);
    }
}