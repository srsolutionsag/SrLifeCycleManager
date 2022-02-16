<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;

/**
 * Class ilSrRule is responsible for storing rule-sets in the database.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutine extends ActiveRecord implements IRoutine
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine';

    /**
     * @var string mysql date format
     */
    protected const MYSQL_DATE_FORMAT = 'Y-m-d';

    /**
     * @var null|int
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_is_primary  true
     * @con_is_notnull  true
     * @con_sequence    true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $id;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      4000
     */
    protected $name;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $ref_id;

    /**
     * @var bool
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      1
     */
    protected $active;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      1
     */
    protected $origin_type;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $owner_id;

    /**
     * @var DateTime
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   date
     */
    protected $creation_date;

    /**
     * @var bool
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      1
     */
    protected $opt_out_possible;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  false
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $elongation_days;

    /**
     * @inheritDoc
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * overrides parent method, transforms mysql data back to it's expected state.
     *
     * @see ActiveRecord::buildFromArray()
     *
     * @param $field_name
     * @param $field_value
     * @return mixed|null
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case self::F_CREATION_DATE:
                return $this->transformStringToDate($field_value);
            case self::F_ACTIVE:
            case self::F_OPT_OUT_POSSIBLE:
                // boolean values are stored as tinyint, therefore
                // (bool) $db_value is used to transform it back.
                return (bool) $field_value;

            default:
                // the original value is used if null gets returned
                return null;
        }
    }

    /**
     * overrides parent method, transforms data to mysql compatible data
     *
     * @see ActiveRecord::getArrayForConnector()
     *
     * @param $field_name
     * @return int|string|null
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case self::F_CREATION_DATE:
                return $this->transformDateToString($field_name);
            case self::F_ACTIVE:
            case self::F_OPT_OUT_POSSIBLE:
                return (int) $this->{$field_name};

            default:
                // the original value is used if null gets returned
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(?int $id) : IRoutine
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name) : IRoutine
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(int $ref_id) : IRoutine
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $is_active) : IRoutine
    {
        $this->active = $is_active;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOriginType() : int
    {
        return $this->origin_type;
    }

    /**
     * @inheritDoc
     */
    public function setOriginType(int $type) : IRoutine
    {
        $this->origin_type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    /**
     * @inheritDoc
     */
    public function setOwnerId(int $owner_id) : IRoutine
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreationDate() : DateTime
    {
        return $this->creation_date;
    }

    /**
     * @inheritDoc
     */
    public function setCreationDate(DateTime $date) : IRoutine
    {
        $this->creation_date = $date;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isOptOutPossible() : bool
    {
        return $this->opt_out_possible;
    }

    /**
     * @inheritDoc
     */
    public function setOptOutPossible(bool $is_possible) : IRoutine
    {
        $this->opt_out_possible = $is_possible;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getElongationDays() : ?int
    {
        return $this->elongation_days;
    }

    /**
     * @inheritDoc
     */
    public function setElongationDays(?int $days) : IRoutine
    {
        $this->elongation_days = $days;
        return $this;
    }

    /**
     * transforms a mysql datetime string into a php datetime object.
     *
     * @param string $value
     * @return DateTime|null
     */
    protected function transformStringToDate(string $value) : ?DateTime
    {
        if (!empty($value)) {
            $datetime = DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $value);
            // returns null if the given value could not be transformed to a DateTime object
            return ($datetime) ?: null;
        }

        return null;
    }

    /**
     * returns a mysql-compatible date string @see ilSrRoutine::MYSQL_DATE_FORMAT.
     *
     * @param string $field_name
     * @return string|null
     */
    protected function transformDateToString(string $field_name) : ?string
    {
        $datetime = $this->{$field_name};
        if (null !== $datetime) {
            /** @var $datetime DateTime */
            return $datetime->format(self::MYSQL_DATE_FORMAT);
        }

        return null;
    }
}