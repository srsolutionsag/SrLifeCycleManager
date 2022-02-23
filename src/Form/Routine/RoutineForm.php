<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Form\AbstractForm;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\IRepository;

use ILIAS\UI\Renderer;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineForm extends AbstractForm
{
    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param IRepository        $repository
     * @param Renderer           $renderer
     * @param RoutineFormBuilder $builder
     */
    public function __construct(
        IRepository $repository,
        Renderer $renderer,
        RoutineFormBuilder $builder
    ) {
        parent::__construct($repository, $renderer, $builder);

        $this->routine = $builder->getRoutine();
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // ensure that at least the routine's ref-id, name and exec dates were submitted.
        return (
            null !== $post_data[RoutineFormBuilder::INPUT_REF_ID] &&
            null !== $post_data[RoutineFormBuilder::INPUT_NAME] &&
            !empty($post_data[RoutineFormBuilder::INPUT_EXECUTION_DATES])
        );
    }

    /**
     * @inheritDoc
     */
    protected function process(array $post_data) : void
    {
        $this->routine
            ->setRefId((int) $post_data[RoutineFormBuilder::INPUT_REF_ID])
            ->setName($post_data[RoutineFormBuilder::INPUT_NAME])
            ->setExecutionDates($post_data[RoutineFormBuilder::INPUT_EXECUTION_DATES])
            ->setActive($post_data[RoutineFormBuilder::INPUT_ACTIVE])
            ->setOptOutPossible($post_data[RoutineFormBuilder::INPUT_OPT_OUT])
        ;

        // if elongation is possible, update the elongation
        // in days attribute. If it's been disabled set the
        // value to null instead.
        if (!empty($post_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE])) {
            $this->routine->setElongationDays((int) $post_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE][RoutineFormBuilder::INPUT_ELONGATION]);
        } else {
            $this->routine->setElongationDays(null);
        }

        $this->repository->routine()->store($this->routine);
    }
}