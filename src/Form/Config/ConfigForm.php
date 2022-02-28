<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\_SrLifeCycleManager\Form\Config;

use srag\Plugins\_SrLifeCycleManager\Form\AbstractForm;
use srag\Plugins\_SrLifeCycleManager\IRepository;

use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfigForm extends AbstractForm
{
    /**
     * @param IRepository       $repository
     * @param Renderer          $renderer
     * @param ConfigFormBuilder $builder
     */
    public function __construct(
        IRepository $repository,
        Renderer $renderer,
        ConfigFormBuilder $builder
    ) {
        parent::__construct($repository, $renderer, $builder);
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // the submitted form_data is always valid, as it's
        // possible all inputs were unchecked or removed.
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function process(array $post_data) : void
    {
        $this->repository->config()->store($post_data);
    }
}