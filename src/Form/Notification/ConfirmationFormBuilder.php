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

use ILIAS\UI\Component\Input\Container\Form\Factory;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Constraint;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationFormBuilder extends NotificationFormBuilder
{
    // NotificationFormBuilder inputs:
    public const INPUT_CONFIRMATION_EVENT = 'input_name_confirmation_event';

    // NotificationFormBuilder language variables:
    protected const MSG_CONFIRMATION_EVENT_ERROR = 'msg_confirmation_event_error';

    protected IConfirmationRepository $repository;

    /**
     * @var string[]
     */
    protected array $event_options;

    /**
     * @param string[] $event_options
     * @param mixed $forms
     * @param mixed $fields
     * @param mixed $refinery
     */
    public function __construct(
        ITranslator $translator,
        Factory $forms,
        \ILIAS\UI\Component\Input\Field\Factory $fields,
        \ILIAS\Refinery\Factory $refinery,
        IConfirmationRepository $repository,
        IConfirmation $notification,
        array $event_options,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $notification, $form_action);
        $this->event_options = $event_options;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    protected function getNotificationSpecificInputs(): array
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
    protected function getConfirmationEventOptions(): array
    {
        $options = [];
        foreach ($this->event_options as $event_name) {
            // only provide the current notification's event or events which
            // aren't already related to another confirmation.
            $confirmation = $this->repository->getByRoutineAndEvent($this->notification->getRoutineId(), $event_name);
            if (null === $confirmation || $this->notification->getEvent() === $event_name) {
                $options[$event_name] = $this->translator->txt($event_name);
            }
        }

        return $options;
    }

    /**
     * Returns a constraint that ensures, that the confirmation for the
     * submitted event only exists once (unless it's the same one being
     * edited).
     */
    protected function getConfirmationEventConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($event): bool {
                if (!is_string($event)) {
                    return false;
                }

                // the constraint fails if the submitted event is unknown.
                if (!in_array($event, $this->event_options, true)) {
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
