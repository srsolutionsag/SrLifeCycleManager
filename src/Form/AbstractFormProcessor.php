<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormProcessor implements IFormProcessor
{
    /**
     * @var UIForm
     */
    protected $form;

    /**
     * @param ServerRequestInterface $request
     * @param UIForm                 $form
     */
    public function __construct(ServerRequestInterface $request, UIForm $form)
    {
        $this->form = $form->withRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function processForm() : bool
    {
        $data = $this->form->getData();
        if (null !== $data && $this->isValid($data)) {
            $this->processData($data);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProcessedForm() : UIForm
    {
        return $this->form;
    }

    /**
     * This method MUST return whether the provided POST-data is valid or not.
     *
     * @param array $post_data
     * @return bool
     */
    abstract protected function isValid(array $post_data) : bool;

    /**
     * This method MUST process the provided POST-data.
     *
     * The method is only called when @see AbstractFormProcessor::isValid()
     * returned true.
     *
     * @param array $post_data
     */
    abstract protected function processData(array $post_data) : void;
}