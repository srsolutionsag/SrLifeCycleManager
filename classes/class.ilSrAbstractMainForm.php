<?php

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Field\Factory;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Class ilSrAbstractMainForm provides derived form classes with
 * common dependencies and functionalities.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilSrAbstractMainForm
{
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * @var Factory
     */
    protected $inputs;

    /**
     * @var ilSrLifeCycleManagerPlugin
     */
    protected $plugin;

    /**
     * @var ilSrLifeCycleManagerRepository
     */
    protected $repository;

    /**
     * @var Standard
     */
    private $form;

    /**
     * @var \ILIAS\DI\UIServices
     */
    private $ui;

    /**
     * ilSrConfigForm constructor
     *
     * @param string $form_action
     */
    public function __construct(string $form_action)
    {
        global $DIC;

        $this->ui         = $DIC->ui();
        $this->http       = $DIC->http();
        $this->inputs     = $DIC->ui()->factory()->input()->field();
        $this->plugin     = ilSrLifeCycleManagerPlugin::getInstance();
        $this->repository = ilSrLifeCycleManagerRepository::getInstance();

        // $this->getFormInputs() may be noticed as wrong return type due
        // to a wrong PHPDoc comment in the UI Service.
        $this->form = $this->ui->factory()->input()->container()->form()->standard(
            $form_action,
            $this->getFormInputs()
        );
    }

    /**
     * This method MUST handle the current form submission.
     * @return bool
     *@see ilSrAbstractMainForm::getFormData() can be used to fetch
     * the forms data for the current request.
     * The boolean return value can be used to check whether
     * the submission was valid or not from outside.
     */
    abstract public function handleFormSubmission() : bool;

    /**
     * This method MUST return a valid UI form-input array, it's
     * used to generate the form within the constructor.
     *
     * @return Input[]
     */
    abstract protected function getFormInputs() : array;

    /**
     * Returns the rendered HTML string of the form provided in
     * @return string
     *@see ilSrAbstractMainForm::$form.
     */
    public function render() : string
    {
        return $this->ui->renderer()->render($this->form);
    }

    /**
     * @return array
     */
    protected function getFormData() : array
    {
        return $this->form->withRequest($this->http->request())->getData();
    }
}