<?php

use srag\Plugins\SrLifeCycleManager\Notification\INotification;

/**
 * Class ilSrNotification is responsible to store notifications in the database.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilSrNotification extends ActiveRecord implements INotification
{
    /**
     * @var string db table name
     */
    public const TABLE_NAME = ilSrLifeCycleManagerPlugin::PLUGIN_ID . '_msg';

    /**
     * ilSrNotification attribute names
     */
    public const F_ID       = 'id';
    public const F_MESSAGE  = 'message';

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
     * @con_fieldtype   clob
     */
    protected $message;

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
    public function setId(int $id) : INotification
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message) : INotification
    {
        $this->message = $message;
        return $this;
    }
}