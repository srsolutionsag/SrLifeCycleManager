<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use ILIAS\Refinery\Constraint;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationFormBuilder extends AbstractFormBuilder
{
    // NotificationFormBuilder inputs:
    public const INPUT_NOTIFICATION_TITLE = 'input_name_notification_title';
    public const INPUT_NOTIFICATION_CONTENT = 'input_name_notification_content';
    public const INPUT_NOTIFICATION_DAYS_BEFORE_SUBMISSION = 'input_name_notification_days_before_submission';

    // NotificationFormBuilder language variables:
    protected const INPUT_NOTIFICATION_CONTENT_INFO = 'input_name_notification_content_info';
    protected const MSG_DAYS_BEFORE_SUBMISSION_ERROR = 'msg_days_before_submission_error';

    /**
     * @var INotification
     */
    protected $notification;

    /**
     * @var INotificationRepository
     */
    protected $repository;

    /**
     * @param ITranslator             $translator
     * @param FormFactory             $forms
     * @param FieldFactory            $fields
     * @param Refinery                $refinery
     * @param INotificationRepository $repository
     * @param INotification           $notification
     * @param string                  $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        INotificationRepository $repository,
        INotification $notification,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->notification = $notification;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getForm() : UIForm
    {
        $inputs[self::INPUT_NOTIFICATION_TITLE] = $this->fields
            ->text($this->translator->txt(self::INPUT_NOTIFICATION_TITLE))
            ->withValue($this->notification->getTitle())
            ->withRequired(true)
        ;
        
        $inputs[self::INPUT_NOTIFICATION_CONTENT] = $this->fields
            ->textarea(
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT),
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT_INFO)
            )
            ->withValue((null !== $this->notification) ?
                $this->notification->getContent() : ''
            )
            ->withRequired(true)
        ;

        $inputs[self::INPUT_NOTIFICATION_DAYS_BEFORE_SUBMISSION] = $this->fields
            ->numeric($this->translator->txt(self::INPUT_NOTIFICATION_DAYS_BEFORE_SUBMISSION))
            ->withRequired(true)
            ->withAdditionalTransformation($this->getDaysBeforeSubmissionConstraint())
            ->withValue(($this->notification->getDaysBeforeSubmission()) ?: null)
        ;

        return $this->forms->standard(
            $this->form_action,
            $inputs
        );
    }

    /**
     * Returns a constraint that ensures, that the submitted amount of days
     * before submission was not already used by another notification.
     *
     * @return Constraint
     */
    protected function getDaysBeforeSubmissionConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($days_before_submission) : bool {
                if (!is_numeric($days_before_submission)) {
                    return false;
                }

                if (null === $this->repository->getByRoutineAndDaysBeforeSubmission(
                    $this->notification->getRoutineId(),
                    $days_before_submission
                )) {
                    return true;
                }

                return false;
            },
            self::MSG_DAYS_BEFORE_SUBMISSION_ERROR
        );
    }
}