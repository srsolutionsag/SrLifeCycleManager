<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminderRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Reminder\IReminder;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ReminderFormBuilder extends NotificationFormBuilder
{
    // NotificationFormBuilder inputs:
    public const INPUT_REMINDER_DAYS_BEFORE_DELETION = 'input_name_reminder_days_before_deletion';

    // NotificationFormBuilder language variables:
    protected const MSG_DAYS_BEFORE_DELETION_ERROR = 'msg_days_before_deletion_error';

    /**
     * @var IReminderRepository
     */
    protected $repository;

    /**
     * @param ITranslator         $translator
     * @param FormFactory         $forms
     * @param FieldFactory        $fields
     * @param Refinery            $refinery
     * @param IReminderRepository $repository
     * @param IReminder           $notification
     * @param string              $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IReminderRepository $repository,
        IReminder $notification,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $notification, $form_action);
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    protected function getNotificationSpecificInputs() : array
    {
        $inputs[self::INPUT_REMINDER_DAYS_BEFORE_DELETION] = $this->fields
            ->numeric($this->translator->txt(self::INPUT_REMINDER_DAYS_BEFORE_DELETION))
            ->withRequired(true)
            ->withAdditionalTransformation($this->getDaysBeforeDeletionConstraint())
            ->withValue(($this->notification->getDaysBeforeDeletion()) ?: null)
        ;

        return $inputs;
    }

    /**
     * Returns a constraint that ensures, that the submitted amount of days
     * before submission was not already used by another notification.
     *
     * @return Constraint
     */
    protected function getDaysBeforeDeletionConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($days_before_deletion) : bool {
                if (!is_numeric($days_before_deletion)) {
                    return false;
                }

                $existing_notification = $this->repository->getByRoutineAndDaysBeforeDeletion(
                    $this->notification->getRoutineId(),
                    (int) $days_before_deletion
                );

                // the constraint only fails if the existing notification for the
                // amount of days before submission is NOT the current one.
                // Otherwise, the notification could never be updated.
                if (null !== $existing_notification) {
                    return ($this->notification->getNotificationId() === $existing_notification->getNotificationId());
                }

                return true;
            },
            $this->translator->txt(self::MSG_DAYS_BEFORE_DELETION_ERROR)
        );
    }
}