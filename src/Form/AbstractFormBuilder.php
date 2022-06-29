<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Form;

use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilObject2;
use Closure;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormBuilder implements IFormBuilder
{
    // AbstractFormBuilder language variables:
    private const MSG_INVALID_RED_IDS = 'msg_invalid_ref_ids';
    private const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';
    private const MSG_INVALID_EMAIL = 'msg_invalid_email';

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var FormFactory
     */
    protected $forms;

    /**
     * @var FieldFactory
     */
    protected $fields;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var string
     */
    protected $form_action;

    /**
     * @param ITranslator  $translator
     * @param FormFactory  $forms
     * @param FieldFactory $fields
     * @param Refinery     $refinery
     * @param string       $form_action
     */
    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        string $form_action
    ) {
        $this->translator = $translator;
        $this->forms = $forms;
        $this->fields = $fields;
        $this->refinery = $refinery;
        $this->form_action = $form_action;
    }

    /**
     * Validates submitted numeric inputs, if the value is not an object ref-id
     * an according message is displayed in the form.
     *
     * @return Constraint
     */
    protected function getRefIdValidationConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            static function(int $ref_id) : bool {
                return (ilObject2::_exists($ref_id, true));
            },
            $this->translator->txt(self::MSG_INVALID_REF_ID)
        );
    }

    /**
     * Behaves similar to @see AbstractFormBuilder::getRefIdValidationConstraint(),
     * but accepts an array of ref-ids that are validated.
     *
     * @return Constraint
     */
    protected function getRefIdArrayValidationConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (array $ref_ids) : bool {
                foreach ($ref_ids as $ref_id) {
                    if (!ilObject2::_exists((int) $ref_id, true)) {
                        return false;
                    }
                }

                return true;
            },
            $this->translator->txt(self::MSG_INVALID_RED_IDS)
        );
    }

    /**
     * Returns a validation constraint for text-inputs that can be used to check
     * if a valid email-address has been submitted.
     *
     * @return Constraint
     */
    protected function getEmailValidationConstraint() : Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (string $email) : bool {
                if (!empty($email)) {
                    return is_string(filter_var($email, FILTER_VALIDATE_EMAIL));
                }

                // the constraint should pass if there was no submitted email.
                return true;
            },
            $this->translator->txt(self::MSG_INVALID_EMAIL)
        );
    }

    /**
     * Extends a tag-input to load tags or options from the given ajax source.
     * To use this, withAdditionalOnLoadCode() must be used with the returned
     * closure.
     *
     * The ajax source must return array[], whereas the sub-arrays have the keys
     * 'value', 'display', and 'searchBy'.
     *
     * Note that this modification only works in ILIAS >= 7, all versions below
     * do not use the 'tagify' library without which the auto-complete does not
     * work.
     *
     * @param string $ajax_action
     * @return Closure
     */
    protected function getTagInputAutoCompleteBinder(string $ajax_action) : Closure
    {
        if (version_compare(ILIAS_VERSION_NUMERIC, '7.0', '<')) {
            return static function ($id) {
            };
        }

        return static function ($id) use ($ajax_action) {
            return "
                var {$id}_requests = [];
                let searchCategories = async function (event) {
                    let tag = il.UI.Input.tagInput.getTagifyInstance('$id')
                    let value = event.detail.value;

                    // abort if value has not at least two characters.
                    // if (1 < value.length) { return; }

                    // show the loading animation and hide the suggestions.
                    tag.loading(true);
                    tag.dropdown.hide();

                    // kill the last request before starting a new one.
                    if (0 < {$id}_requests.length) {
                        for (let i = 0; i < {$id}_requests.length; i++) {
                            {$id}_requests[i].abort();
                        }
                    }

                    // fetch suggestions asynchronously and store the
                    // current request in the array.
                    {$id}_requests.push($.ajax({
                        type: 'GET',
                        url: encodeURI('$ajax_action' + '&term=' + value),
                        success: response => {
                            // update whitelist, hide loading animation and
                            // show the suggestions.
                            tag.settings.whitelist = response;
                            tag.loading(false);
                            tag.dropdown.show();
                        },
                    }));
                }

                $(document).ready(function () {
                    let tag = il.UI.Input.tagInput.getTagifyInstance('$id');

                    // enforceWhitelist will make the whitelist persistent,
                    // previously found objects will therefore stay in it. 
                    tag.on('input', searchCategories);
                });
            ";
        };
    }
}