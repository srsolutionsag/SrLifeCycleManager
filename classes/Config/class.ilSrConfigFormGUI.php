<?php

use ILIAS\DI\HTTPServices;
use ILIAS\UI\Interfaces\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * ilSrConfigFormGUI is responsible for the configuration form.
 *
 * @TODO: refactor this, maybe implement some sort of standard plugin|component
 *        configuration service.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilSrConfigFormGUI
{
    /**
     * ilSrConfigFormGUI lang vars
     */
    private const MSG_NO_TAXONOMIES     = 'msg_cnf_no_taxonomies';
    private const MSG_NO_SUB_TAXONOMIES = 'msg_cnf_no_sub_taxonomies';
    private const MSG_INVALID_EMAIL     = 'msg_cnf_invalid_email';
    private const MSG_INVALID_REF_ID    = 'msg_cnf_invalid_ref_id';
    private const MSG_MISSING_INPUTS    = 'msg_cnf_missing_inputs';
    private const MAIL_TEXT_PLACEHOLDER = 'mail_text_placeholders';

    /**
     * @var ilSrARConfig[]
     */
    private $config;

    /**
     * @var ilSrCourseManagerPlugin
     */
    private $plugin;

    /**
     * @var HTTPServices
     */
    private $http;

    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var Refinery
     */
    private $refinery;

    /**
     * @var ilSrCourseManagerRepository
     */
    private $repository;

    /**
     * @var Standard
     */
    private $form;

    /**
     * ilSrConfigFormGUI constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->http       = $DIC->http();
        $this->ctrl       = $DIC->ctrl();
        $this->refinery   = $DIC->refinery();
        $this->factory    = $DIC->ui()->factory();
        $this->renderer   = $DIC->ui()->renderer();
        $this->repository = ilSrCourseManagerRepository::getInstance();
        $this->plugin     = ilSrCourseManagerPlugin::getInstance();
        $this->config     = ilSrARConfig::get();

        $this->initForm();
    }

    /**
     * initializes the form component and it's input fields.
     *
     */
    private function initForm() : void
    {
        $inputs = [];

        $inputs[ilSrARConfig::CNF_CLERK_EMAIL] = $this->factory
            ->input()->field()->text($this->plugin->txt(ilSrARConfig::CNF_CLERK_EMAIL))
            ->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CLERK_EMAIL])) ?
                $this->config[ilSrARConfig::CNF_CLERK_EMAIL]->getValue() : ''
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    $this->getEmailValidationCallable()
                )
            )
        ;

        $inputs[ilSrARConfig::CNF_CLERK_ROLE] = $this->factory
            ->input()->field()->select(
                $this->plugin->txt(ilSrARConfig::CNF_CLERK_ROLE),
                $this->repository->getAvailableGlobalRoles()
            )
            ->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CLERK_ROLE])) ?
                (string) $this->config[ilSrARConfig::CNF_CLERK_ROLE]->getValue() : ''
            )
        ;

        $inputs[ilSrARConfig::CNF_CLERK_AREA] = $this->factory
            ->input()->field()->text($this->plugin->txt(ilSrARConfig::CNF_CLERK_AREA))
            ->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CLERK_AREA])) ?
                (string) $this->config[ilSrARConfig::CNF_CLERK_AREA]->getValue() : ''
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    $this->getRefIdValidationCallable()
                )
            )
        ;

        $inputs[ilSrARConfig::CNF_TUTOR_ROLE] = $this->factory
            ->input()->field()->select(
                $this->plugin->txt(ilSrARConfig::CNF_TUTOR_ROLE),
                $this->repository->getAvailableGlobalRoles()
            )
            ->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_TUTOR_ROLE])) ?
                (string) $this->config[ilSrARConfig::CNF_TUTOR_ROLE]->getValue() : ''
            )
        ;

        $group_role = $this->factory
            ->input()->field()->group([
                $this->factory->
                    input()->field()->select(
                        $this->plugin->txt(ilSrARConfig::CNF_CLIENT_ROLE),
                        $this->repository->getAvailableGlobalRoles()
                    )->withValue((!empty($this->config[ilSrARConfig::CNF_CLIENT_ROLE])) ?
                        (string) $this->config[ilSrARConfig::CNF_CLIENT_ROLE]->getValue() : ''
                    )
                ,
            ],
                $this->plugin->txt(ilSrARConfig::CNF_CLIENT_ROLE)
            )
        ;

        $group_position = $this->factory
            ->input()->field()->group([
                $this->factory
                    ->input()->field()->select(
                        $this->plugin->txt(ilSrARConfig::CNF_CLIENT_POSITION),
                        $this->repository->getAvailableOrgUnitPositions()
                    )->withValue((!empty($this->config[ilSrARConfig::CNF_CLIENT_POSITION])) ?
                        (string) $this->config[ilSrARConfig::CNF_CLIENT_POSITION]->getValue() : ''
                    )
                ,
            ],
                $this->plugin->txt(ilSrARConfig::CNF_CLIENT_POSITION)
            )
        ;

        $inputs[ilSrARConfig::CNF_CLIENT_IDENTIFIER] = $this->factory
            ->input()->field()->switchableGroup([
                ilSrARConfig::CNF_CLIENT_ROLE => $group_role,
                ilSrARConfig::CNF_CLIENT_POSITION => $group_position
            ],
                $this->plugin->txt(ilSrARConfig::CNF_CLIENT_IDENTIFIER)
            )->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CLIENT_IDENTIFIER])) ?
                (string) $this->config[ilSrARConfig::CNF_CLIENT_IDENTIFIER]->getValue() :
                ilSrARConfig::CNF_CLIENT_ROLE
            )
        ;

        $inputs[ilSrARConfig::CNF_CLIENT_AREA] = $this->factory
            ->input()->field()->text($this->plugin->txt(ilSrARConfig::CNF_CLIENT_AREA))
            ->withRequired(true)
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CLIENT_AREA])) ?
                (string) $this->config[ilSrARConfig::CNF_CLIENT_AREA]->getValue() : ''
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    $this->getRefIdValidationCallable()
                )
            )
        ;

        $inputs[ilSrARConfig::CNF_CAN_CLIENT_JOIN] = $this->factory
            ->input()->field()->checkbox($this->plugin->txt(ilSrARConfig::CNF_CAN_CLIENT_JOIN))
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CAN_CLIENT_JOIN])) ?
                (bool) $this->config[ilSrARConfig::CNF_CAN_CLIENT_JOIN]->getValue() : false
            )
        ;

        $inputs[ilSrARConfig::CNF_MAIL_ORDER_NEW] = $this->factory
            ->input()->field()->textarea(
                $this->plugin->txt(ilSrARConfig::CNF_MAIL_ORDER_NEW),
                $this->plugin->txt(self::MAIL_TEXT_PLACEHOLDER)
            )->withValue((!empty($this->config[ilSrARConfig::CNF_MAIL_ORDER_NEW])) ?
                (string) $this->config[ilSrARConfig::CNF_MAIL_ORDER_NEW]->getValue() : ''
            )
        ;

        $inputs[ilSrARConfig::CNF_MAIL_ORDER_PROCESSED] = $this->factory
            ->input()->field()->textarea(
                $this->plugin->txt(ilSrARConfig::CNF_MAIL_ORDER_PROCESSED),
                $this->plugin->txt(self::MAIL_TEXT_PLACEHOLDER)
            )
            ->withValue((!empty($this->config[ilSrARConfig::CNF_MAIL_ORDER_PROCESSED])) ?
                (string) $this->config[ilSrARConfig::CNF_MAIL_ORDER_PROCESSED]->getValue() : ''
            )
        ;

        $this->form = $this->factory
            ->input()->container()->form()->standard(
                $this->ctrl->getFormActionByClass(
                    ilSrConfigGUI::class,
                    ilSrConfigGUI::CMD_CONFIG_SAVE
                ),
                $inputs
            )
        ;
    }

    /**
     * returns a closure that validates if the given value is a ilObject ref-id
     * and returns it if so.
     *
     * @return Closure
     */
    private function getRefIdValidationCallable() : Closure
    {
        return static function($ref_id) {
            // check if value is integer and proceed
            if (preg_match('/^(\d+)$/', $ref_id)) {
                $ref_id = (int) $ref_id;
                if (ilObject2::_exists($ref_id, true)) {
                    return $ref_id;
                }
            }

            return null;
        };
    }

    /**
     * returns a closure that validates the given email address and returns it if so.
     *
     * @return Closure
     */
    private function getEmailValidationCallable() : Closure
    {
        return static function(string $email) : ?string {
            return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : null;
        };
    }

    /**
     * checks if the required inputs contain data and returns a fitting
     * error message if not.
     *
     * actual validation happens with additionalTransformation()
     * @see ilSrConfigFormGUI::initForm()
     *
     * @param null|array $data
     * @return string|null
     */
    private function validateData(?array $data) : ?string
    {
        if (null === $data) return self::MSG_MISSING_INPUTS;

        switch ($data) {
            case empty($data[ilSrARConfig::CNF_CLERK_EMAIL]):
                return self::MSG_INVALID_EMAIL;
            case empty($data[ilSrARConfig::CNF_CLERK_AREA]):
            case empty($data[ilSrARConfig::CNF_CLIENT_AREA]):
                return self::MSG_INVALID_REF_ID;
            case (empty($data[ilSrARConfig::CNF_CLERK_ROLE]) ||
                  empty($data[ilSrARConfig::CNF_TUTOR_ROLE]) ||
                  empty($data[ilSrARConfig::CNF_CLIENT_IDENTIFIER])):
                return self::MSG_MISSING_INPUTS;
            default:
                return null;
        }
    }

    /**
     * returns an existing or new ilSrARConfig for given $identifier.
     *
     * @param string $identifier
     * @return ilSrARConfig
     * @throws arException
     */
    private function getConfig(string $identifier) : ilSrARConfig
    {
        $config = ilSrARConfig::where([ilSrARConfig::IDENTIFIER => $identifier], '=')->first();
        if (null === $config) {
            $config = new ilSrARConfig();
            $config->setIdentifier($identifier);
        }

        return $config;
    }

    /**
     * stores changed input field values into ilSrARConfig and returns an error
     * message if any required data is invalid.
     *
     * @return null|string null or error message
     * @throws arException
     */
    public function saveConfig() : ?string
    {
        $form = $this->form->withRequest($this->http->request());
        $data = $form->getData();

        if (null !== ($msg = $this->validateData($data))) return $msg;

        foreach ($data as $identifier => $value) {
            if (ilSrARConfig::CNF_CLIENT_IDENTIFIER === $identifier) {
                // fetch config that determines the client identifier
                $this->getConfig($identifier)
                     ->setValue($value[0])
                     ->store();

                // retrieve chosen identifier and value from switchable group
                $identifier = $value[0];
                $value      = $value[1][0];
            }

            // HOTFIX: sets tax-children to [] if no sub taxonomies for new CNF_CRS_TAX_TREE are found.
            // this must be done because somehow if no multi-select input was checked the HTTPService
            // doesn't consider these input fields anymore.
            // if (ilSrARConfig::CNF_CRS_TAX_TREE === $identifier &&
            //     empty($this->repository->getSubTaxonomiesForTaxonomy((int) $value))
            // ) {
            //     $this->getConfig(ilSrARConfig::CNF_CRS_TAX_CHILDREN)
            //          ->setValue([])
            //          ->store();
            // }

            $this->getConfig($identifier)
                 ->setValue($value)
                 ->store();
        }

        return null;
    }

    /**
     * "legacy" variant of taxonomy inputs. This has been replaced with Rules, but stays here anyways.
     * you know. just in case.
     *
     * @return \ILIAS\UI\Implementation\Component\Input\Field\Input[]
     */
    private function legacyTaxonomyInputs() : array
    {
        $inputs = [];
        $query_param     = ilSrConfigGUI::TAXONOMY_QUERY_PARAM;
        $main_taxonomies = $this->repository->getClerkAreaMainTaxonomies();
        $inputs[ilSrARConfig::CNF_CRS_TAX_TREE] = $this->factory
            ->input()->field()->select(
                $this->plugin->txt(ilSrARConfig::CNF_CRS_TAX_TREE),
                $main_taxonomies
            )->withValue((!empty($this->config[ilSrARConfig::CNF_CRS_TAX_TREE])) ?
                $this->config[ilSrARConfig::CNF_CRS_TAX_TREE]->getValue() : ''
            )
            ->withAdditionalOnLoadCode(function($id) use ($query_param) {
                return "
                    let parseTaxonomyChildren = function(taxonomies) {
                        // fetch input-name and increment it to use for sub-taxes
                        let main_tax_input    = $('#{$id}');
                        let main_tax_input_id = parseInt(main_tax_input.attr('name').replace(/form_input_(\d+)+/g, '$1'));
                        let sub_tax_name      = 'form_input_' + (main_tax_input_id + 1) + '[]';
                    
                        // fetch multi-select ul from sub_tax_group children
                        let main_tax_group = $(main_tax_input.parents()[1]);
                        let sub_tax_group  = main_tax_group.next();                        
                        let sub_tax_list   = $($(sub_tax_group.children()[1]).children()[0]);

                        sub_tax_list.empty();
                        
                        // check if an empty array or json response was returned
                        if (typeof taxonomies.length === 'undefined') {       
                            Object.keys(taxonomies).forEach(function(key) {
                                sub_tax_list.append(`
                                    <li>
                                        <input type='checkbox' name='` + sub_tax_name + `' value='` + key + `' />
                                        <span>` + taxonomies[key] + `</span>
                                    </li>
                                `);
                            });
                        } else {
                            sub_tax_list.append(`
                                <li>
                                    <span>` + '{$this->plugin->txt(self::MSG_NO_SUB_TAXONOMIES)}' + `</span>
                                </li>
                            `);
                        }                                              
                    }
                    
                    let fetchSelectedTaxonomyChildren = async function(tax_id) {
                        if (!tax_id) return;
                    
                        let error = false;
                        await $.ajax({
                            url: '{$this->ctrl->getLinkTargetByClass(
                                ilSrConfigGUI::class,
                                ilSrConfigGUI::CMD_CONFIG_SEARCH,
                                "",
                                true
                            )}',
                            data: {
                                {$query_param}: tax_id,
                            },
                            type: 'GET',
                            success: function(response) {
                                parseTaxonomyChildren(response);
                            },
                            error: function(e) {
                                console.error('Error while fetching selected taxonomy children : ', e)
                                error = true;
                            }
                        });
                    
                        if (error) {
                            // @TODO: handle it.
                        }
                    }
                    
                    $('#{$id}').change(function(e) {
                        fetchSelectedTaxonomyChildren($(this).val());
                    });
                ";
            })
        ;

        if (empty($main_taxonomies)) {
            $inputs[ilSrARConfig::CNF_CRS_TAX_TREE]
                ->withDisabled(true)
                ->withByline(
                    $this->plugin->txt(self::MSG_NO_TAXONOMIES)
                )
            ;
        }

        $sub_taxonomies = $this->repository->getClerkAreaSubTaxonomies();
        $inputs[ilSrARConfig::CNF_CRS_TAX_CHILDREN] = $this->factory
            ->input()->field()->multiSelect(
                $this->plugin->txt(ilSrARConfig::CNF_CRS_TAX_CHILDREN),
                $sub_taxonomies
            )
            ->withDisabled(empty($sub_taxonomies))
            ->withValue((!empty($this->config[ilSrARConfig::CNF_CRS_TAX_CHILDREN]) && !empty($sub_taxonomies)) ?
                (array) $this->config[ilSrARConfig::CNF_CRS_TAX_CHILDREN]->getValue() : []
            )
            ->withAdditionalOnLoadCode(static function($id) {
                return "
                    // removes this input's empty checkbox element if it has no value. 
                    $(document).ready(function() {
                        let first_child = $($('#{$id} input:checkbox')[0]);
                        if (!first_child.val()) {
                            $('#{$id}').empty();
                        }
                    });
                ";
            })
        ;

        if (empty($sub_taxonomies)) {
            $inputs[ilSrARConfig::CNF_CRS_TAX_CHILDREN]->withDisabled(true);
        }
    }

    /**
     * returns the rendered form html.
     *
     * @return string
     */
    public function getHTML() : string
    {
        return $this->renderer->render($this->form);
    }
}
