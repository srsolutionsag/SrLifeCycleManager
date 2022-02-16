<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Builder\Form\FormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineNotification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationFormBuilder extends FormBuilder
{
    public const INPUT_NOTIFICATION_MESSAGE = 'input_notification_message';
    public const INPUT_NOTIFICATION_DAYS    = 'input_notification_days';

    /**
     * @var INotification|null
     */
    protected $notification = null;

    /**
     * @var IRoutineNotification|null
     */
    protected $routine_relation = null;

    /**
     * @param INotification|null $notification
     * @return $this
     */
    public function withNotification(?INotification $notification) : self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param IRoutineNotification|null $routine_relation
     * @return $this
     */
    public function withRoutineRelation(?IRoutineNotification $routine_relation) : self
    {
        $this->routine_relation = $routine_relation;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getForm(string $form_action) : Form
    {
        $inputs = [];

        $inputs[INotification::F_MESSAGE] = $this->input_factory
            ->textarea($this->translate(self::INPUT_NOTIFICATION_MESSAGE))
            ->withValue((null !== $this->notification) ?
                $this->notification->getMessage() : null
            )
        ;

        $inputs[IRoutineNotification::F_DAYS_BEFORE_SUBMISSION] = $this->input_factory
            ->text($this->translate(self::INPUT_NOTIFICATION_DAYS))
            ->withAdditionalTransformation($this->refinery->numeric()->isNumeric())
            ->withAdditionalTransformation($this->refinery->to()->int())
            ->withValue((null !== $this->routine_relation) ?
                $this->routine_relation->getDaysBeforeSubmission() : null
            )
        ;

        return $this->form_factory->standard(
            $form_action,
            $inputs
        );
    }
}