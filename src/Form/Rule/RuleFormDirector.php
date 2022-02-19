<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Rule;

use srag\Plugins\SrLifeCycleManager\Rule\IRoutineAwareRule;
use srag\Plugins\SrLifeCycleManager\IRepository;

use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RuleFormDirector
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var RuleFormBuilder
     */
    protected $builder;

    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * @var IRoutineAwareRule
     */
    protected $rule;

    /**
     * @param Renderer          $renderer
     * @param RuleFormBuilder   $builder
     * @param IRepository       $repository
     * @param IRoutineAwareRule $rule
     */
    public function __construct(
        Renderer $renderer,
        RuleFormBuilder $builder,
        IRepository $repository,
        IRoutineAwareRule $rule
    ) {
        $this->renderer = $renderer;
        $this->builder = $builder;
        $this->repository = $repository;
        $this->rule = $rule;
    }

    /**
     * @return RuleForm
     */
    public function getStandardForm() : RuleForm
    {
        return new RuleForm(
            $this->repository,
            $this->renderer,
            $this->builder
                ->setRule($this->rule)
                ->addCommonAttributes()
                ->addCourseAttributes()
                ->addGroupAttributes()
        );
    }

    /**
     * @return RuleForm
     */
    public function getCourseAttributeForm() : RuleForm
    {
        return new RuleForm(
            $this->repository,
            $this->renderer,
            $this->builder
                ->setRule($this->rule)
                ->addCommonAttributes()
                ->addCourseAttributes()
        );
    }

    /**
     * @return RuleForm
     */
    public function getGroupAttributeForm() : RuleForm
    {
        return new RuleForm(
            $this->repository,
            $this->renderer,
            $this->builder
                ->setRule($this->rule)
                ->addCommonAttributes()
                ->addGroupAttributes()
        );
    }
}