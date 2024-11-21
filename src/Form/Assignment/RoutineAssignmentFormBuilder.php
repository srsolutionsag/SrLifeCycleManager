<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form\Assignment;

use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormBuilder extends AbstractAssignmentFormBuilder
{
    /**
     * @var IRoutine[]
     */
    protected $unassigned_routines;

    /**
     * @param IRoutine[] $unassigned_routines
     * @inheritDoc
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutineAssignment $assignment,
        array $all_routines,
        array $unassigned_routines,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $assignment, $all_routines, $form_action);
        $this->unassigned_routines = $unassigned_routines;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutineInput(): Input
    {
        return $this->fields->multiSelect(
            $this->translator->txt(self::INPUT_ROUTINE),
            $this->getRoutineOptions($this->unassigned_routines)
        )->withRequired(true);
    }

    /**
     * @inheritDoc
     */
    protected function getObjectInput(): Input
    {
        return $this->getImmutableObjectInput();
    }
}
