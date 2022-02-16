<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Builder\Form\IFormImplementation;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Renderer;

/**
 * Class ilSrAbstractMainForm provides derived form classes with
 * common dependencies and functionalities.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ilSrAbstractForm implements IFormImplementation
{
    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * @var ilTemplate
     */
    protected $global_template;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilTemplate                     $global_template
     * @param Renderer                       $renderer
     * @param Form                           $form
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilTemplate $global_template,
        Renderer $renderer,
        Form $form
    ) {
        $this->repository = $repository;
        $this->global_template = $global_template;
        $this->renderer = $renderer;
        $this->form = $form;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(ServerRequestInterface $request) : bool
    {
        // clone the form with data from the given request, this
        // also enables the form to be rendered with the error-
        // messages provided by the input transformations.
        $this->form = $this->form->withRequest($request);

        // call the derived classes validation method to check
        // whether the data should be processed.
        $form_data = $this->form->getData();
        if (null !== $form_data && $this->validateFormData($form_data)) {
            $this->handleFormData($form_data);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function printToGlobalTemplate() : void
    {
        $this->global_template->setContent(
            $this->renderer->render($this->form)
        );
    }

    /**
     * This method MUST validate the submitted form-data.
     *
     * @param array $form_data
     * @return bool
     */
    abstract protected function validateFormData(array $form_data) : bool;

    /**
     * This method MUST handle the data of the current submission.
     *
     * The boolean return value can be used to check whether
     * the submission was valid or not from outside.
     *
     * @param array      $form_data
     */
    abstract protected function handleFormData(array $form_data) : void;
}