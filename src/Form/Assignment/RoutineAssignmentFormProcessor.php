<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormProcessor extends AbstractFormProcessor
{
    /**
     * @var IRoutineAssignmentRepository
     */
    protected $repository;

    /**
     * @var IRoutineAssignment
     */
    protected $assignment;

    /**
     * @param IRoutineAssignmentRepository $repository
     * @param IRoutineAssignment           $assignment
     * @param ServerRequestInterface       $request
     * @param UIForm                       $form
     */
    public function __construct(
        IRoutineAssignmentRepository $repository,
        IRoutineAssignment $assignment,
        ServerRequestInterface $request,
        UIForm $form
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->assignment = $assignment;
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data) : bool
    {
        // ensure that required fields were submitted.
        return (
            !empty($post_data[RoutineAssignmentFormBuilder::INPUT_ROUTINE]) &&
            !empty($post_data[RoutineAssignmentFormBuilder::INPUT_REF_ID])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        if (is_array($post_data[RoutineAssignmentFormBuilder::INPUT_ROUTINE]) &&
            null === $this->assignment->getRoutineId()
        ) {
            $this->processMultipleRoutines($post_data);
            return;
        }

        if (is_array($post_data[RoutineAssignmentFormBuilder::INPUT_REF_ID]) &&
            null === $this->assignment->getRefId()
        ) {
            $this->processMultipleRefIds($post_data);
            return;
        }

        $this->processStandardAssignment($post_data);
    }

    /**
     * Creates or updates multiple routine assignments for the submitted ref-id.
     *
     * @param array $post_data
     * @return void
     */
    protected function processMultipleRoutines(array $post_data) : void
    {
        $this->assignment
            ->setRecursive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_RECURSIVE])
            ->setActive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_ACTIVE])
        ;

        // all data except $routine_id stays persistent, therefore we store the
        // same assignment for different routines.
        foreach ($post_data[RoutineAssignmentFormBuilder::INPUT_ROUTINE] as $routine_id) {
            $this->repository->store($this->assignment->setRoutineId((int) $routine_id));
        }
    }

    /**
     * Creates or updates multiple routine assignments for the submitted routine_id.
     *
     * @param array $post_data
     * @return void
     */
    protected function processMultipleRefIds(array $post_data) : void
    {
        $this->assignment
            ->setRecursive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_RECURSIVE])
            ->setActive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_ACTIVE])
        ;

        // all data except $ref_id stays persistent, therefore we store the
        // same assignment for different objects.
        foreach ($post_data[RoutineAssignmentFormBuilder::INPUT_REF_ID] as $ref_id) {
            $this->repository->store($this->assignment->setRefId((int) $ref_id));
        }
    }

    /**
     * Creates or updates an existing assignment for the submitted data.
     *
     * @param array $post_data
     * @return void
     */
    protected function processStandardAssignment(array $post_data) : void
    {
        $this->repository->store(
            $this->assignment
                ->setRoutineId((int) $post_data[RoutineAssignmentFormBuilder::INPUT_ROUTINE])
                ->setRefId((int) $post_data[RoutineAssignmentFormBuilder::INPUT_REF_ID])
                ->setRecursive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_RECURSIVE])
                ->setActive($post_data[RoutineAssignmentFormBuilder::INPUT_IS_ACTIVE])
        );
    }
}