<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Config\Config;
use ILIAS\DI\RBACServices;

/**
 * This repository is responsible for all config CRUD operation.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrConfigRepository implements IConfigRepository
{
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var RBACServices
     */
    protected $rbac;

    /**
     * @param ilDBInterface $database
     * @param RBACServices  $rbac
     */
    public function __construct(ilDBInterface $database, RBACServices $rbac)
    {
        $this->database = $database;
        $this->rbac = $rbac;
    }

    /**
     * @inheritDoc
     */
    public function get() : IConfig
    {
        $query = "SELECT identifier, configuration FROM srlcm_configuration;";
        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        $config = new Config();
        if (empty($results)) {
            return $config;
        }

        foreach ($results as $query_result) {
            switch ($query_result[IConfig::F_IDENTIFIER]) {
                case IConfig::CNF_PRIVILEGED_ROLES:
                    $roles = (!empty($query_result[IConfig::F_CONFIG])) ?
                        explode(',', $query_result[IConfig::F_CONFIG]) : []
                    ;

                    $config->setPrivilegedRoles($roles);
                    break;

                case IConfig::CNF_SHOW_ROUTINES_IN_REPOSITORY:
                    $config->setShowRoutinesInRepository((bool) $query_result[IConfig::F_CONFIG]);
                    break;

                case IConfig::CNF_CREATE_ROUTINES_IN_REPOSITORY:
                    $config->setCreateRoutinesInRepository((bool) $query_result[IConfig::F_CONFIG]);
                    break;
            }
        }

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function store(IConfig $config) : IConfig
    {
        $this->updateConfig(IConfig::CNF_PRIVILEGED_ROLES, implode(',', $config->getPrivilegedRoles()));
        $this->updateConfig(IConfig::CNF_SHOW_ROUTINES_IN_REPOSITORY, (string) $config->showRoutinesInRepository());
        $this->updateConfig(IConfig::CNF_CREATE_ROUTINES_IN_REPOSITORY, (string) $config->createRoutinesInRepository());

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableGlobalRoles() : array
    {
        $role_options = [];
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);
        if (empty($global_roles)) {
            return $role_options;
        }

        foreach ($global_roles as $role_data) {
            $role_id = (int) $role_data['obj_id'];
            // the administrator role can be ignored, as this
            // role should always be able to do everything.
            if ((int) SYSTEM_ROLE_ID !== $role_id) {
                $role_title = ilObjRole::_getTranslation($role_data['title']);

                // map the role-title to its role id associatively.
                $role_options[$role_id] = $role_title;
            }
        }

        return $role_options;
    }

    /**
     * @param string $identifier
     * @param string $value
     * @return void
     */
    protected function updateConfig(string $identifier, string $value) : void
    {
        $query = "UPDATE srlcm_configuration SET configuration = %s WHERE identifier = %s;";

        $this->database->manipulateF(
            $query,
            ['text', 'text'],
            [
                $value,
                $identifier,
            ]
        );
    }
}