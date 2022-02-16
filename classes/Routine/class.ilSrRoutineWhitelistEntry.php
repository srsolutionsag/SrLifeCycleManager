<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineWhitelistEntry;

/**
 * Class ilSrRule is responsible for storing rule-sets in the database.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineWhitelistEntry extends ActiveRecord implements IRoutineWhitelistEntry
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_routine_w_list';

    /**
     * @var string mysql date format
     */
    private const MYSQL_DATE_FORMAT = 'Y-m-d';

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
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      1
     */
    protected $whitelist_type;

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $routine_id;

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
     * @var \DateTime|null
     *
     * @con_has_field   true
     * @con_is_notnull  false
     * @con_fieldtype   date
     */
    protected $active_until;

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
            case self::F_ACTIVE_UNTIL:
                return $this->transformStringToDate($field_value);

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
     * @return mixed|null
     */
    public function sleep($field_name) : ?string
    {
        switch ($field_name) {
            case self::F_ACTIVE_UNTIL:
                return $this->transformDateToString($field_name);

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
    public function setId(?int $id) : IRoutineWhitelistEntry
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getWhitelistType() : int
    {
        return $this->whitelist_type;
    }

    /**
     * @inheritDoc
     */
    public function setWhitelistType(int $whitelist_type) : IRoutineWhitelistEntry
    {
        $this->whitelist_type = $whitelist_type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId() : int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id) : IRoutineWhitelistEntry
    {
        $this->routine_id = $routine_id;
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
    public function setRefId(int $ref_id) : IRoutineWhitelistEntry
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getActiveUntil() : ?DateTime
    {
        return $this->active_until;
    }

    /**
     * @inheritDoc
     */
    public function setActiveUntil(?DateTime $date) : IRoutineWhitelistEntry
    {
        $this->active_until = $date;
        return $this;
    }

    /**
     * transforms a mysql datetime string into a php datetime object.
     *
     * @param string $value
     * @return DateTime|null
     */
    private function transformStringToDate(string $value) : ?DateTime
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
    private function transformDateToString(string $field_name) : ?string
    {
        $datetime = $this->{$field_name};
        if (null !== $datetime) {
            /** @var $datetime DateTime */
            $datetime->format(self::MYSQL_DATE_FORMAT);
        }

        return null;
    }
}