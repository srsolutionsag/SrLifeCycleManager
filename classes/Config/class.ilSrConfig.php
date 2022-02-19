<?php // strict types are not possible with ActiveRecord.

use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * Class ilSrConfig stores all general plugin configurations.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This class is used to store any sort of value for a specific CONFIGURATION_IDENTIFIER.
 * Since any type of value is accepted by setValue(), the data will be encoded to
 * JSON and stored as TEXT in the database. Therefore values have to be type-casted
 * by the developer in most cases before used.
 *
 * setValue() and getValue() although distinguish arrays from other values to save
 * developers the trouble of exploding strings. Therefore getValue() will return both,
 * strings and arrays.
 *
 * - general usage:
 *
 *      - load configuration:
 *
 *          $config = ilSrConfig::get();
 *          $option = $config[ilSrConfig::<<CONFIGURATION_IDENTIFIER>>]->getValue();
 *
 *          or
 *
 *          $config = ilSrConfig::find(ilSrConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $option = $config->getValue();
 *
 *      - update configuration:
 *
 *          $config = ilSrConfig::find(ilSrConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $config
 *              ->setValue(mixed $value)
 *              ->store();
 */
class ilSrConfig extends ActiveRecord implements IConfig
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_config';

    /**
     * @var string identifier name
     */
    public const IDENTIFIER = 'identifier';

    /**
     * @var string regex pattern for identifier validation
     */
    private const IDENTIFIER_REGEX = '/^[A-Za-z0-9_-]*$/';

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
     * @inheritDoc
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier) : IConfig
    {
        // thrown exception can be ignored, as this is ONLY for
        // development purposes.
        $this->validateIdentifier($identifier);
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function setValue($value) : IConfig
    {
        if (!is_array((array) $value)) {
            // lowercase string values for easier comparison
            $value = strtolower((string) $value);
        }

        $this->value = json_encode($value);
        return $this;
    }

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
}