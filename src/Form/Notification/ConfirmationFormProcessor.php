<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Notification;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmationRepository;
use srag\Plugins\SrLifeCycleManager\Notification\Confirmation\IConfirmation;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfirmationFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IConfirmationRepository
     */
    protected $repository;

    /**
     * @var IConfirmation
     */
    protected $notification;

    /**
     * @param IConfirmationRepository $repository
     * @param ServerRequestInterface  $request
     * @param UIForm                  $form
     * @param IConfirmation           $notification
     */
    public function __construct(
        IConfirmationRepository $repository,
        ServerRequestInterface $request,
        UIForm $form,
        IConfirmation $notification
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->notification = $notification;
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
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
    protected function processData(array $post_data) : void
    {
        $this->notification
            ->setTitle($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_TITLE])
            ->setContent($post_data[ConfirmationFormBuilder::INPUT_NOTIFICATION_CONTENT])
            ->setEvent($post_data[ConfirmationFormBuilder::INPUT_CONFIRMATION_EVENT])
        ;

        $this->repository->store($this->notification);
    }
}