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
class AssignmentFormProcessor extends AbstractFormProcessor
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
     * @inheritdoc
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
            !empty($post_data[AbstractAssignmentFormBuilder::INPUT_ROUTINE]) &&
            !empty($post_data[AbstractAssignmentFormBuilder::INPUT_REF_ID])
        );
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        if (null !== $this->assignment->getRoutineId() &&
            null !== $this->assignment->getRefId()
        ) {
            $this->repository->store(
                $this->assignment
                    ->setRecursive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_RECURSIVE])
                    ->setActive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_ACTIVE])
            );

            return;
        }

        if (is_array($post_data[AbstractAssignmentFormBuilder::INPUT_ROUTINE]) &&
            null === $this->assignment->getRoutineId()
        ) {
            $this->processMultipleRoutines($post_data);
        }

        if (is_array($post_data[AbstractAssignmentFormBuilder::INPUT_REF_ID]) &&
            null === $this->assignment->getRefId()
        ) {
            $this->processMultipleRefIds($post_data);
        }
    }

    /**
     * Creates or updates multiple routine assignments for the submitted ref-id.
     *
     * Note that the value of @see RoutineAssignment::$ref_id must not be set, since
     * the DTO is passed by reference and already set up in @see \ilSrAbstractAssignmentGUI.
     *
     * This also serves as some kind of manipulation protection because even if the
     * clientside HTML-value of the immutable input was modified it doesn't matter.
     *
     * @param array $post_data
     * @return void
     */
    protected function processMultipleRoutines(array $post_data) : void
    {
        $this->assignment
            ->setRecursive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_RECURSIVE])
            ->setActive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_ACTIVE])
        ;

        // all data except $routine_id stays persistent, therefore we store the
        // same assignment for different routines.
        foreach ($post_data[AbstractAssignmentFormBuilder::INPUT_ROUTINE] as $routine_id) {
            $this->repository->store($this->assignment->setRoutineId((int) $routine_id));
        }
    }

    /**
     * Creates or updates multiple routine assignments for the submitted routine_id.
     *
     * Note that the value of @see RoutineAssignment::$routine_id must not be set, since
     * the DTO is passed by reference and already set up in @see \ilSrAbstractAssignmentGUI.
     *
     * This also serves as some kind of manipulation protection because even if the
     * clientside HTML-value of the immutable input was modified it doesn't matter.
     *
     * @param array $post_data
     * @return void
     */
    protected function processMultipleRefIds(array $post_data) : void
    {
        $this->assignment
            ->setRecursive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_RECURSIVE])
            ->setActive($post_data[AbstractAssignmentFormBuilder::INPUT_IS_ACTIVE])
        ;

        // all data except $ref_id stays persistent, therefore we store the
        // same assignment for different objects.
        foreach ($post_data[AbstractAssignmentFormBuilder::INPUT_REF_ID] as $ref_id) {
            $this->repository->store($this->assignment->setRefId((int) $ref_id));
        }
    }
}