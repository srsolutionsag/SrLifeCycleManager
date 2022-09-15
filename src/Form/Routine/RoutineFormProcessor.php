<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form\Routine;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IRoutineRepository
     */
    protected $repository;

    /**
     * @var IRoutine
     */
    protected $routine;

    /**
     * @param IRoutineRepository     $repository
     * @param ServerRequestInterface $request
     * @param UIForm                 $form
     * @param IRoutine               $routine
     */
    public function __construct(
        IRoutineRepository $repository,
        ServerRequestInterface $request,
        UIForm $form,
        IRoutine $routine
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->routine = $routine;
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // ensure that required fields were submitted (might be redundant).
        return (
            null !== $post_data[RoutineFormBuilder::INPUT_TITLE] &&
            !empty($post_data[RoutineFormBuilder::INPUT_ROUTINE_TYPE])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        $this->routine
            ->setTitle($post_data[RoutineFormBuilder::INPUT_TITLE])
            ->setRoutineType($post_data[RoutineFormBuilder::INPUT_ROUTINE_TYPE])
            ->setOptOut($post_data[RoutineFormBuilder::INPUT_HAS_OPT_OUT])
        ;

        // if elongation is possible, update the elongation
        // in days attribute. If it's been disabled set the
        // value to null instead.
        if (!empty($post_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE])) {
            $this->routine->setElongation((int) $post_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE][RoutineFormBuilder::INPUT_ELONGATION]);
            $this->routine->setElongationCooldown((int) $post_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE][RoutineFormBuilder::INPUT_ELONGATION_COOLDOWN]);
        } else {
            $this->routine->setElongation(null);
            $this->routine->setElongationCooldown(null);
        }

        $this->repository->store($this->routine);
    }
}