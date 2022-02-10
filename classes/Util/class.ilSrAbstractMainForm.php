<?php

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\UIServices;

/**
 * Class ilSrAbstractMainForm provides derived form classes with
 * common dependencies and functionalities.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class ilSrAbstractMainForm
{
    /**
     * ilSrAbstractMainForm data-type names
     */
    protected const TYPE_CAST_INT = 'int';
    protected const TYPE_CAST_BOOL = 'bool';
    protected const TYPE_CAST_FLOAT = 'float';

    /**
     * @var string[] supported data-types.
     */
    private const TYPE_CAST_TYPES = [
        self::TYPE_CAST_INT,
        self::TYPE_CAST_BOOL,
        self::TYPE_CAST_FLOAT,
    ];

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var FieldFactory
     */
    protected $inputs;

    /**
     * @var Refinery
     */
    protected $refinery;

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
     * @var UIServices
     */
    private $ui;

    /**
     * ilSrConfigForm constructor
     */
    public function __construct(
        UIServices $ui,
        ilCtrl $ctrl,
        Refinery $refinery,
        ilSrLifeCycleManagerPlugin $plugin,
        ilSrLifeCycleManagerRepository $repository
    ) {
        $this->ui         = $ui;
        $this->ctrl       = $ctrl;
        $this->inputs     = $ui->factory()->input()->field();
        $this->refinery   = $refinery;
        $this->plugin     = $plugin;
        $this->repository = $repository;

        // initialize the form with the form-action and form-inputs
        // provided by derived classes. (Note that getFormInputs()
        // may be noticed as wrong value-type, due to error in UI
        // service phpdoc-comment).
        $this->form = $ui->factory()->input()->container()->form()->standard(
            $this->getFormAction(),
            $this->getFormInputs()
        );
    }

    /**
     * This method MUST return the forms action (link-target).
     *
     * $this->ctrl can be used to access ilCtrl and build a
     * form-action with @see ilCtrl::getFormActionByClass().
     *
     * @return string
     */
    abstract protected function getFormAction() : string;

    /**
     * This method MUST return a valid UI form-input array, it's
     * used to generate the form within the constructor.
     *
     * @return Input[]
     */
    abstract protected function getFormInputs() : array;

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

    /**
     * Handles the form submission for the given request.
     *
     * The data is extracted from the given request and validated
     * by the derived classes method. If it's valid the data gets
     * processed.
     *
     * The boolean return value can be used to check whether or
     * not the form needs to be rendered (to show errors).
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function handleRequest(ServerRequestInterface $request) : bool
    {
        // clone the form with data from the given request, this
        // also enables the form to be rendered with the error-
        // messages provided by the input transformations.
        $this->form = $this->form->withRequest($request);

        // call the derived classes validation method to check
        // whether or not the data should be processed.
        $form_data = $this->form->getData();
        if (null !== $form_data && $this->validateFormData($form_data)) {
            $this->handleFormData($form_data);
            return true;
        }

        return false;
    }

    /**
     * Returns the rendered HTML string of this form.
     *
     * @return string
     */
    public function render() : string
    {
        return $this->ui->renderer()->render($this->form);
    }

    /**
     * returns a closure that can be used to check whether
     * a ref-id still exists or not.
     *
     * Note that the closure needs an integer value as an argument,
     * hence before calling it in an input transformation a further
     * transformation needs to be prepended for the type-casting.
     *
     * @see ilSrAbstractMainForm::getTypeCastClosure()
     *
     * @return Closure
     */
    protected function getRefIdValidationClosure() : Closure
    {
        return static function(int $ref_id) : ?int {
            return (ilObject2::_exists($ref_id, true)) ? $ref_id : null;
        };
    }

    /**
     * Returns a closure that can be used for type-casting input
     * values with additional transformations.
     *
     * Note that the type-casting WILL NOT be checked, so if the
     * provided value cannot be casted an error is thrown.
     *
     * @param string $type (int|bool|float)
     * @return Closure
     */
    protected function getTypeCastClosure(string $type) : Closure
    {
        if (!in_array($type, self::TYPE_CAST_TYPES, true)) {
            throw new LogicException("'$type' is not supported by " . self::class . '::getTypeCastClosure');
        }

        return static function($value) use ($type) {
            // casts the given value to the $type passed when
            // retrieving this closure.
            settype($value, $type);
            return $value;
        };
    }
}