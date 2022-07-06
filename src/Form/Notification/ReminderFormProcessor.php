<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ReminderFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IReminderRepository
     */
    protected $repository;

    /**
     * @var IReminder
     */
    protected $notification;

    /**
     * @param IReminderRepository    $repository
     * @param ServerRequestInterface $request
     * @param UIForm                 $form
     * @param IReminder              $notification
     */
    public function __construct(
        IReminderRepository $repository,
        ServerRequestInterface $request,
        UIForm $form,
        IReminder $notification
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
            !empty($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_TITLE]) &&
            !empty($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_CONTENT]) &&
            !empty($post_data[ReminderFormBuilder::INPUT_REMINDER_DAYS_BEFORE_DELETION])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        $this->notification
            ->setTitle($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_TITLE])
            ->setContent($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_CONTENT])
            ->setDaysBeforeDeletion((int) $post_data[ReminderFormBuilder::INPUT_REMINDER_DAYS_BEFORE_DELETION])
        ;

        $this->repository->store($this->notification);
    }
}