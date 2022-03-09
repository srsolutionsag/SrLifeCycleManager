<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Config;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Config\Config;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ConfigFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IConfigRepository
     */
    protected $repository;

    /**
     * @param IConfigRepository      $repository
     * @param ServerRequestInterface $request
     * @param UIForm                 $form
     */
    public function __construct(
        IConfigRepository $repository,
        ServerRequestInterface $request,
        UIForm $form
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
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
    protected function processData(array $post_data) : void
    {
        $this->repository->store(
            new Config(
                $post_data[IConfig::CNF_PRIVILEGED_ROLES] ?? [],
                $post_data[IConfig::CNF_SHOW_ROUTINES_IN_REPOSITORY],
                $post_data[IConfig::CNF_CREATE_ROUTINES_IN_REPOSITORY]
            )
        );
    }
}