<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Input;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class NotificationFormBuilder extends AbstractFormBuilder
{
    // NotificationFormBuilder inputs:
    public const INPUT_NOTIFICATION_TITLE = 'input_name_notification_title';
    public const INPUT_NOTIFICATION_CONTENT = 'input_name_notification_content';

    // NotificationFormBuilder language variables:
    protected const INPUT_NOTIFICATION_CONTENT_INFO = 'input_name_notification_content_info';
    protected const INPUT_NOTIFICATION_TITLE_INFO = 'input_name_notification_title_info';

    /**
     * @var INotification
     */
    protected $notification;

    /**
     * @param ITranslator   $translator
     * @param FormFactory   $forms
     * @param FieldFactory  $fields
     * @param Refinery      $refinery
     * @param INotification $notification
     * @param string        $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        INotification $notification,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->notification = $notification;
    }

    /**
     * @inheritDoc
     */
    public function getForm(): UIForm
    {
        $inputs[self::INPUT_NOTIFICATION_TITLE] = $this->fields
            ->text(
                $this->translator->txt(self::INPUT_NOTIFICATION_TITLE),
                $this->translator->txt(self::INPUT_NOTIFICATION_TITLE_INFO)
            )
            ->withValue($this->notification->getTitle())
            ->withRequired(true)
        ;

        $inputs[self::INPUT_NOTIFICATION_CONTENT] = $this->fields
            ->textarea(
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT),
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT_INFO)
            )
            ->withValue(
                (null !== $this->notification) ?
                $this->notification->getContent() : ''
            )
            ->withRequired(true)
        ;

        return $this->forms->standard(
            $this->form_action,
            array_merge($inputs, $this->getNotificationSpecificInputs())
        );
    }

    /**
     * Returns all notification-specific inputs by the implementation of
     * this form builder.
     *
     * @return Input[]
     */
    abstract protected function getNotificationSpecificInputs(): array;
}
