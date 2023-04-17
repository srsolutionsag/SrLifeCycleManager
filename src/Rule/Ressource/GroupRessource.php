<?php

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Ressource;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupRessource implements IDatabaseRessource, IObjectRessource, IParticipantRessource
{
    /**
     * @var \ilDBInterface
     */
    protected $database;

    /**
     * @var \ilObjGroup
     */
    protected $group;

    public function __construct(\ilDBInterface $database, \ilObjGroup $group)
    {
        $this->database = $database;
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function getDatabase(): \ilDBInterface
    {
        return $this->database;
    }

    /**
     * @inheritDoc
     */
    public function getObject(): \ilObject
    {
        return $this->group;
    }

    /**
     * @inheritDoc
     */
    public function getParticipants(): \ilParticipants
    {
        return $this->group->getMembersObject();
    }

    public function getGroup(): \ilObjGroup
    {
        return $this->group;
    }
}
