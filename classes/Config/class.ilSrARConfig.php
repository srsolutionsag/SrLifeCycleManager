<?php

/**
 * ilSrARConfig stores all plugin configurations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class is used to store any sort of value for a specific CONFIGURATION_IDENTIFIER.
 * Since any type of value is accepted by the setValue(), the data will be encoded to
 * JSON and stored as TEXT in the database. Therefore values have to be type-casted in
 * most cases before used.
 *
 * setValue() and getValue() although distinguish arrays from other values to save
 * developers the trouble of exploding strings. Therefore getValue() will return both,
 * strings and arrays.
 *
 * - general usage:
 *
 *      - load configuration:
 *
 *          $config = ilSrARConfig::get();
 *          $option = $config[ilSrARConfig::<<CONFIGURATION_IDENTIFIER>>]->getValue();
 *
 *          or
 *
 *          $config = ilSrARConfig::find(ilSrARConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $option = $config->getValue();
 *
 *      - update configuration:
 *
 *          $config = ilSrARConfig::find(ilSrARConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $config
 *              ->setValue(mixed $value)
 *              ->store();
 */
final class ilSrARConfig extends ActiveRecord
{
    /**
     * @var string primary key regex pattern
     */
    private const IDENTIFIER_REGEX = '/^[A-Za-z0-9_-]*$/';

    /**
     * @var string active record table name
     */
    public const TABLE_NAME = ilSrCourseManagerPlugin::PLUGIN_ID . '_config';

    /**
     * @var string identifier name
     */
    public const IDENTIFIER = 'identifier';

    /**
     * configuration identifiers
     */
    public const CNF_CLERK_EMAIL          = 'cnf_clerk_email';
    public const CNF_CLERK_ROLE           = 'cnf_clerk_role';
    public const CNF_CLERK_AREA           = 'cnf_clerk_area';
    public const CNF_TUTOR_ROLE           = 'cnf_tutor_role';
    public const CNF_CLIENT_ROLE          = 'cnf_client_role';
    public const CNF_CLIENT_POSITION      = 'cnf_client_position';
    public const CNF_CLIENT_IDENTIFIER    = 'cnf_client_identifier';
    public const CNF_CLIENT_AREA          = 'cnf_client_area';
    public const CNF_CAN_CLIENT_JOIN      = 'cnf_can_client_join';
    public const CNF_CRS_TAX_TREE         = 'cnf_crs_tax_tree';
    public const CNF_CRS_TAX_CHILDREN     = 'cnf_crs_tax_children';
    public const CNF_PARTICIPANT_ROLE     = 'cnf_participant_role';
    public const CNF_MAIL_ORDER_NEW       = 'cnf_mail_order_new';
    public const CNF_MAIL_ORDER_PROCESSED = 'cnf_mail_order_processed';

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_is_primary  true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      250
     */
    protected $identifier;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      4000
     */
    protected $value;

    /**
     * checks primary key value for prohibited characters.
     *
     * @param string $identifier
     * @throws arException
     */
    private function validateIdentifier(string $identifier) : void
    {
        if (!preg_match(self::IDENTIFIER_REGEX, $identifier)) {
            throw new arException(
                arException::UNKNONWN_EXCEPTION,
                'Prohibited characters in primary key value $identifier: ' . $identifier
            );
        }
    }

    /**
     * @return string
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

//    /**
//     * returns a cache storage instance.
//     *
//     * @return ilGlobalCache
//     */
//    public function getCache() : ilGlobalCache
//    {
//        return ilGlobalCache::getInstance(ilGlobalCache::COMP_PLUGINS);
//    }

    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return ilSrARConfig
     * @throws arException
     */
    public function setIdentifier(string $identifier) : ilSrARConfig
    {
        $this->validateIdentifier($identifier);
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getValue()
    {
        $value = json_decode($this->value, true);
        if (empty($value)) return null;
        if (is_array($value) && !empty((array) $value)) {
            return (array) $value;
        }

        // remove quotes which come from json_decode() in strings
        return trim($value, '"');
    }

    /**
     * @param mixed $value
     * @return ilSrARConfig
     */
    public function setValue($value) : ilSrARConfig
    {
        if (!is_array((array) $value)) {
            // lowercase string values for easier comparison
            $value = strtolower((string) $value);
        }

        $this->value = json_encode($value);
        return $this;
    }
}