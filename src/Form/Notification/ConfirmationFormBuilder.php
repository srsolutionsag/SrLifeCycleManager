<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use srag\Plugins\SrLifeCycleManager\Routine\RoutineEvent;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationFormBuilder extends NotificationFormBuilder
{
    // NotificationFormBuilder inputs:
    public const INPUT_CONFIRMATION_EVENT = 'input_name_confirmation_event';

    // NotificationFormBuilder language variables:
    protected const MSG_CONFIRMATION_EVENT_ERROR = 'msg_confirmation_event_error';

    /**
     * @var IConfirmationRepository
     */
    protected $repository;

    /**
     * @param ITranslator             $translator
     * @param FormFactory             $forms
     * @param FieldFactory            $fields
     * @param Refinery                $refinery
     * @param IConfirmationRepository $repository
     * @param IConfirmation           $notification
     * @param string                  $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IConfirmationRepository $repository,
        IConfirmation $notification,
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
        $inputs[self::INPUT_CONFIRMATION_EVENT] = $this->fields
            ->select(
                $this->translator->txt(self::INPUT_CONFIRMATION_EVENT),
                $this->getConfirmationEventOptions()
            )
            ->withRequired(true)
            ->withAdditionalTransformation($this->getConfirmationEventConstraint())
            ->withValue(($this->notification->getEvent()));

        return $inputs;
    }

    /**
     * Returns all possible confirmation events as event => translation pairs.
     *
     * @return array<string, string>
     */
    protected function getConfirmationEventOptions() : array
    {
        $options = [];
        foreach (RoutineEvent::ACTIONS as $action) {
            // only provide the current notification's event or events which
            // aren't already related to another confirmation.
            $confirmation = $this->repository->getByRoutineAndEvent($this->notification->getRoutineId(), $action);
            if (null === $confirmation || $this->notification->getEvent() === $action) {
                $options[$action] = $this->translator->txt($action);
            }
        }

        return $options;
    }

    /**
     * Returns a constraint that ensures, that the confirmation for the
     * submitted event only exists once (unless it's the same one being
     * edited).
     *
     * @return Constraint
     */
    protected function getConfirmationEventConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($event) : bool {
                if (!is_string($event)) {
                    return false;
                }

                // the constraint fails if the submitted event is unknown.
                if (!in_array($event, RoutineEvent::ACTIONS, true)) {
                    return false;
                }

                $existing_notification = $this->repository->getByRoutineAndEvent(
                    $this->notification->getRoutineId(),
                    $event
                );

                // the constraint only fails if the existing notification for the
                // submitted event is NOT the current one. Otherwise, the notification
                // could never be updated.
                if (null !== $existing_notification) {
                    return ($this->notification->getNotificationId() === $existing_notification->getNotificationId());
                }

                return true;
            },
            $this->translator->txt(self::MSG_CONFIRMATION_EVENT_ERROR)
        );
    }
}