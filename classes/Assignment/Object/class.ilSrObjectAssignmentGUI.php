<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Form\Assignment\ObjectAssignmentFormBuilder;
use srag\Plugins\SrLifeCycleManager\Form\IFormBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\GlobalHttpState;

/**
 * This class is responsible for assigning multiple or one object to
 * exactly one routine.
 *
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The GUI must therefore be provided with @see ilSrAbstractGUI::PARAM_ROUTINE_ID,
 * Otherwise the constructor will throw an exception.
 * Optionally, @see ilSrObjectAssignmentGUI::PARAM_ASSIGNED_REF_ID can be provided,
 * which means an existing assignment is edited.
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrObjectAssignmentGUI extends ilSrAbstractAssignmentGUI
{
    // ilSrRoutineAssignmentGUI assignment ref-id parameter:
    public const PARAM_ASSIGNED_REF_ID = 'assigned_ref_id';

    // ilSrObjectAssignmentGUI command names:
    public const CMD_OBJECT_SEARCH = 'searchObjects';

    /**
     * @var IFormBuilder
     */
    protected $form_builder;

    /**
     * @var GlobalHttpState
     */
    protected $http;

    /**
     * Throws an exception if the request doesn't provide routine-id.
     *
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->panicOnMissingRoutine();

        // this gui must use another parameter name because it is called within
        // the administration context, that handles another functionality over
        // the 'ref_id' query- parameter.
        $this->ctrl->saveParameterByClass(self::class, self::PARAM_ASSIGNED_REF_ID);

        $this->http = $DIC->http();
        $this->form_builder = new ObjectAssignmentFormBuilder(
            $this->translator,
            $this->ui_factory->input()->container()->form(),
            $this->ui_factory->input()->field(),
            $this->refinery,
            $this->assignment,
            $this->repository->routine()->getAll(),
            $this->getFormAction(self::CMD_ASSIGNMENT_SAVE),
            $this->getAjaxSource()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAssignmentRefIdParameter(): string
    {
        return self::PARAM_ASSIGNED_REF_ID;
    }

    /**
     * @inheritDoc
     */
    protected function index(): void
    {
        $table = new ilSrObjectAssignmentTable(
            $this->ui_factory,
            $this->renderer,
            $this->translator,
            $this->repository->general(),
            $this->access_handler,
            $this->ctrl,
            $this,
            self::CMD_INDEX,
            $this->repository->assignment()->getAllByRoutineId($this->routine->getRoutineId(), true)
        );

        $this->tab_manager->addBackToRoutines();
        $this->toolbar_manager->addObjectAssignmentToolbar();
        $this->render($table->getTable());
    }

    /**
     * This method searches objects by the requested term and returns
     * them asynchronously (as a json-response).
     *
     * @see AbstractFormBuilder::getTagInputAutoCompleteBinder()
     */
    protected function searchObjects(): void
    {
        $body = $this->request->getQueryParams();
        $term = $body['term'] ?? '';

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody(
                    Streams::ofString(
                        json_encode(
                            $this->repository->general()->getObjectsByTypeAndTerm(
                                $this->routine->getRoutineType(),
                                $term
                            )
                        )
                    )
                )
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Returns a link pointing to @see ilSrObjectAssignmentGUI::searchObjects(),
     * which is used for the tag-input-autocomplete in ILIAS>=7.
     *
     * @return string
     */
    protected function getAjaxSource(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_OBJECT_SEARCH,
            '',
            true
        );
    }

    /**
     * @inheritDoc
     */
    protected function getForm(): Form
    {
        return $this->form_builder->getForm();
    }
}
