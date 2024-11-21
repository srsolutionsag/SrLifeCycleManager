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
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ObjectAssignmentFormBuilder extends AbstractAssignmentFormBuilder
{
    /**
     * @var string
     */
    protected $ajax_source;

    /**
     * @param string $ajax_source
     * @inheritDoc
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        IRoutineAssignment $assignment,
        array $all_routines,
        string $form_action,
        string $ajax_source
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $assignment, $all_routines, $form_action);
        $this->ajax_source = $ajax_source;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutineInput(): Input
    {
        return $this->getImmutableRoutineInput();
    }

    /**
     * @inheritDoc
     */
    protected function getObjectInput(): Input
    {
        return $this->fields->tag(
            $this->translator->txt(self::INPUT_REF_ID),
            [] // all inputs are user-generated.
        )->withAdditionalOnLoadCode(
            $this->getTagInputAutoCompleteBinder($this->ajax_source)
        )->withRequired(true);
    }
}
