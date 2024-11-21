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
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormBuilder;
use ILIAS\UI\Component\Input\Field\Input;
use srag\Plugins\SrLifeCycleManager\Notification\INotification;
use srag\Plugins\SrLifeCycleManager\ITranslator;

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
     * @param ITranslator   $translator
     * @param mixed         $forms
     * @param mixed         $fields
     * @param mixed         $refinery
     * @param INotification $notification
     * @param string        $form_action
     */
    public function __construct(
        ITranslator $translator,
        Factory $forms,
        \ILIAS\UI\Component\Input\Field\Factory $fields,
        \ILIAS\Refinery\Factory $refinery,
        protected INotification $notification,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
    }

    /**
     * @inheritDoc
     */
    public function getForm(): Form
    {
        $inputs[self::INPUT_NOTIFICATION_TITLE] = $this->fields
            ->text(
                $this->translator->txt(self::INPUT_NOTIFICATION_TITLE),
                $this->translator->txt(self::INPUT_NOTIFICATION_TITLE_INFO)
            )
            ->withValue($this->notification->getTitle())
            ->withRequired(true);

        $inputs[self::INPUT_NOTIFICATION_CONTENT] = $this->fields
            ->textarea(
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT),
                $this->translator->txt(self::INPUT_NOTIFICATION_CONTENT_INFO)
            )
            ->withValue(
                (null !== $this->notification) ?
                    $this->notification->getContent() : ''
            )
            ->withRequired(true);

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
