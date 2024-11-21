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

use ILIAS\UI\Component\Input\Container\Form\Factory;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignment;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class RoutineAssignmentFormBuilder extends AbstractAssignmentFormBuilder
{
    /**
     * @var IRoutine[]
     */
    protected array $unassigned_routines;

    /**
     * @param IRoutine[] $unassigned_routines
     * @inheritDoc
     * @param mixed $forms
     * @param mixed $fields
     * @param mixed $refinery
     */
    public function __construct(
        ITranslator $translator,
        Factory $forms,
        \ILIAS\UI\Component\Input\Field\Factory $fields,
        \ILIAS\Refinery\Factory $refinery,
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
