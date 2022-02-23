<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Notification\IRoutineAwareNotification;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use srag\Plugins\SrLifeCycleManager\ITranslator;

use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NotificationFormBuilder extends AbstractFormBuilder
{
    public const INPUT_NOTIFICATION_MESSAGE = 'input_name_notification_message';
    public const INPUT_NOTIFICATION_DAYS = 'input_name_notification_days';

    protected const INPUT_NOTIFICATION_MESSAGE_INFO = 'input_name_notification_message_info';

    /**
     * @var IRoutineAwareNotification
     */
    protected $notification;

    /**
     * @param FormFactory               $form_factory
     * @param InputFactory              $input_factory
     * @param Refinery                  $refinery
     * @param ITranslator               $translator
     * @param string                    $form_action
     * @param IRoutineAwareNotification $notification
     */
    public function __construct(
        FormFactory $form_factory,
        InputFactory $input_factory,
        Refinery $refinery,
        ITranslator $translator,
        string $form_action,
        IRoutineAwareNotification $notification
    ) {
        parent::__construct($form_factory, $input_factory, $refinery, $translator, $form_action);

        $this->notification = $notification;
    }

    /**
     * @return IRoutineAwareNotification
     */
    public function getNotification() : IRoutineAwareNotification
    {
        return $this->notification;
    }

    /**
     * @inheritDoc
     */
    protected function getInputs() : array
    {
        $inputs[self::INPUT_NOTIFICATION_MESSAGE] = $this->input_factory
            ->textarea(
                $this->translate(self::INPUT_NOTIFICATION_MESSAGE),
                $this->translate(self::INPUT_NOTIFICATION_MESSAGE_INFO)
            )
            ->withValue((null !== $this->notification) ?
                $this->notification->getMessage() : ''
            )
            ->withRequired(true)
        ;

        $inputs[self::INPUT_NOTIFICATION_DAYS] = $this->input_factory
            ->numeric($this->translate(self::INPUT_NOTIFICATION_DAYS))
            ->withRequired(true)
            ->withValue(($this->notification->getDaysBeforeSubmission()) ?: null)
        ;

        return $inputs;
    }
}