<?php declare(strict_types=1);

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrLifeCycleManager\Builder\Form\Routine\RoutineFormBuilder;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRoutineForm
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineForm extends ilSrAbstractForm
{
    /**
     * @var IRoutine|null
     */
    protected $routine;

    /**
     * @var int
     */
    protected $origin_type;

    /**
     * @var int
     */
    protected $owner_id;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilGlobalTemplateInterface      $global_template
     * @param Renderer                       $renderer
     * @param Form                           $form
     * @param int                            $origin_type
     * @param int                            $owner_id
     * @param IRoutine|null                  $routine
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilGlobalTemplateInterface $global_template,
        Renderer $renderer,
        Form $form,
        int $origin_type,
        int $owner_id,
        IRoutine $routine = null
    ) {
        parent::__construct($repository, $global_template, $renderer, $form);

        $this->routine = $routine;
        $this->origin_type = $origin_type;
        $this->owner_id = $owner_id;
    }

    /**
     * @inheritDoc
     */
    protected function validateFormData(array $form_data) : bool
    {
        // ensures that at least the routine's ref-id
        // and name must are submitted.
        return (null !== $form_data[RoutineFormBuilder::INPUT_REF_ID] &&
                null !== $form_data[RoutineFormBuilder::INPUT_NAME])
        ;
    }

    /**
     * @inheritDoc
     */
    protected function handleFormData(array $form_data) : void
    {
        if (null === $this->routine) {
            $this->routine = $this->repository->routine()->getEmpty(
                $this->origin_type,
                $this->owner_id
            );
        }

        $this->routine
            ->setRefId($form_data[RoutineFormBuilder::INPUT_REF_ID])
            ->setName($form_data[RoutineFormBuilder::INPUT_NAME])
            ->setActive($form_data[RoutineFormBuilder::INPUT_ACTIVE])
            ->setOptOutPossible($form_data[RoutineFormBuilder::INPUT_OPT_OUT])
        ;

        // if elongation is possible, update the elongation
        // in days attribute. If it's been disabled set the
        // value to null instead.
        if (!empty($form_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE])) {
            $this->routine->setElongationDays($form_data[RoutineFormBuilder::INPUT_ELONGATION_POSSIBLE][RoutineFormBuilder::INPUT_ELONGATION]);
        } else {
            $this->routine->setElongationDays(null);
        }

        $this->repository->routine()->store($this->routine);
    }
}