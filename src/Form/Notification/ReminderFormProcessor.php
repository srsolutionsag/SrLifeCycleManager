<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ReminderFormProcessor extends AbstractFormProcessor
{
    /**
     * @param IReminderRepository    $repository
     * @param ServerRequestInterface $request
     * @param mixed                  $form
     * @param IReminder              $notification
     */
    public function __construct(
        protected IReminderRepository $repository,
        ServerRequestInterface $request,
        Form $form,
        protected IReminder $notification
    ) {
        parent::__construct($request, $form);
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data): bool
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
    protected function processData(array $post_data): void
    {
        $this->notification
            ->setTitle($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_TITLE])
            ->setContent($post_data[ReminderFormBuilder::INPUT_NOTIFICATION_CONTENT])
            ->setDaysBeforeDeletion((int) $post_data[ReminderFormBuilder::INPUT_REMINDER_DAYS_BEFORE_DELETION]);

        $this->repository->store($this->notification);
    }
}
