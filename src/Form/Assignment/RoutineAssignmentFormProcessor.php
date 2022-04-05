<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Form\AbstractFormProcessor;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;

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
        // TODO: Implement isValid() method.
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data) : void
    {
        // TODO: Implement processData() method.
    }
}