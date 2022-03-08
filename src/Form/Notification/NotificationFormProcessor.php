<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationFormProcessor extends AbstractFormProcessor
{
    /**
     * @var INotificationRepository
     */
    protected $repository;

    /**
     * @var INotification
     */
    protected $notification;

    /**
     * @param INotificationRepository $repository
     * @param ServerRequestInterface  $request
     * @param UIForm                  $form
     * @param INotification           $notification
     */
    public function __construct(
        INotificationRepository $repository,
        ServerRequestInterface $request,
        UIForm $form,
        INotification $notification
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->notification = $notification;
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // ensure that the required values are not empty.
        return (
            !empty($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_TITLE]) &&
            !empty($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_CONTENT]) &&
            !empty($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_DAYS_BEFORE_SUBMISSION])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        $this->notification
            ->setTitle($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_TITLE])
            ->setContent($post_data[NotificationFormBuilder::INPUT_NOTIFICATION_CONTENT])
            ->setDaysBeforeSubmission((int) $post_data[NotificationFormBuilder::INPUT_NOTIFICATION_DAYS_BEFORE_SUBMISSION])
        ;

        $this->repository->store($this->notification);
    }
}