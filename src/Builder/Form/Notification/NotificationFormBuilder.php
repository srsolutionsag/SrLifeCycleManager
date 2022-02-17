<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Builder\Form\FormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationFormBuilder extends FormBuilder
{
    public const INPUT_NOTIFICATION_MESSAGE = 'input_notification_message';
    public const INPUT_NOTIFICATION_DAYS    = 'input_notification_days';

    /**
     * @var IRoutineAwareNotification|null
     */
    protected $notification = null;

    /**
     * @param IRoutineAwareNotification|null $notification
     * @return $this
     */
    public function withNotification(?IRoutineAwareNotification $notification) : self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getForm(string $form_action) : Form
    {
        $inputs = [];

        $inputs[self::INPUT_NOTIFICATION_MESSAGE] = $this->input_factory
            ->textarea($this->translate(self::INPUT_NOTIFICATION_MESSAGE))
            ->withValue((null !== $this->notification) ?
                $this->notification->getMessage() : ''
            )
            ->withRequired(true)
        ;

        $inputs[self::INPUT_NOTIFICATION_DAYS] = $this->input_factory
            ->numeric($this->translate(self::INPUT_NOTIFICATION_DAYS))
            ->withRequired(true)
            ->withValue((null !== $this->notification) ?
                $this->notification->getDaysBeforeSubmission() : null
            )
        ;

        return $this->form_factory->standard(
            $form_action,
            $inputs
        );
    }
}