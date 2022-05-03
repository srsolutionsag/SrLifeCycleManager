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
        $is_tool_enabled = (null !== $post_data[IConfig::CNF_TOOL_IS_ENABLED]);
        $show_routines = ($is_tool_enabled) ? $post_data[IConfig::CNF_TOOL_IS_ENABLED][IConfig::CNF_TOOL_SHOW_ROUTINES] : false;
        $show_controls = ($is_tool_enabled) ? $post_data[IConfig::CNF_TOOL_IS_ENABLED][IConfig::CNF_TOOL_SHOW_CONTROLS] : false;

        $this->repository->store(
            new Config(
                $post_data[IConfig::CNF_TOOL_SHOW_ROUTINES] ?? [],
                $post_data[IConfig::CNF_ROLE_MANAGE_ASSIGNMENTS] ?? [],
                $is_tool_enabled,
                $show_routines,
                $show_controls,
                $post_data[IConfig::CNF_CUSTOM_FROM_EMAIL]
            )
        );
    }
}