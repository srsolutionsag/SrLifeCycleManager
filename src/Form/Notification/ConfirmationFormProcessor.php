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
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationFormProcessor extends AbstractFormProcessor
{
    /**
     * @param IConfirmationRepository $repository
     * @param ServerRequestInterface  $request
     * @param mixed                   $form
     * @param IConfirmation           $notification
     */
    public function __construct(
        protected IConfirmationRepository $repository,
        ServerRequestInterface $request,
        Form $form,
        protected IConfirmation $notification
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
            !empty($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_TITLE]) &&
            !empty($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_CONTENT]) &&
            !empty($post_data[ConfirmationFormBuilder::INPUT_CONFIRMATION_EVENT])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data): void
    {
        $this->notification
            ->setTitle($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_TITLE])
            ->setContent($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_CONTENT])
            ->setEvent($post_data[ConfirmationFormBuilder::INPUT_CONFIRMATION_EVENT]);

        $this->repository->store($this->notification);
    }
}
