<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use srag\Plugins\SrLifeCycleManager\IRepository;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractForm implements IForm
{
    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var IFormBuilder
     */
    protected $builder;

    /**
     * @var UIForm
     */
    private $form;

    /**
     * @param IRepository  $repository
     * @param Renderer     $renderer
     * @param IFormBuilder $builder
     */
    public function __construct(
        IRepository $repository,
        Renderer $renderer,
        IFormBuilder $builder
    ) {
        $this->repository = $repository;
        $this->renderer = $renderer;
        $this->builder = $builder;
        $this->form = $builder->getForm();
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(ServerRequestInterface $request) : bool
    {
        $this->form = $this->form->withRequest($request);
        $post_data  = $this->form->getData();

        if (null !== $post_data && $this->isValid($post_data)) {
            $this->process($post_data);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function render() : string
    {
        return $this->renderer->render($this->form);
    }

    /**
     * @param array $post_data
     * @return bool
     */
    abstract protected function isValid(array $post_data) : bool;

    /**
     * @param array $post_data
     */
    abstract protected function process(array $post_data) : void;
}