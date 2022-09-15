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
    protected const ARRAY_STRING_SEPARATOR = ',';

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
                case IConfig::CNF_ROLE_MANAGE_ROUTINES:
                    $config->setManageRoutineRoles(
                        $this->stringToArray($query_result[IConfig::F_CONFIG])
                    );
                    break;

                case IConfig::CNF_ROLE_MANAGE_ASSIGNMENTS:
                    $config->setManageAssignmentRoles(
                        $this->stringToArray($query_result[IConfig::F_CONFIG])
                    );
                    break;

                case IConfig::CNF_TOOL_IS_ENABLED:
                    $config->setToolEnabled((bool) $query_result[IConfig::F_CONFIG]);
                    break;

                case IConfig::CNF_TOOL_SHOW_ROUTINES:
                    $config->setShouldToolShowRoutines((bool) $query_result[IConfig::F_CONFIG]);
                    break;

                case IConfig::CNF_TOOL_SHOW_CONTROLS:
                    $config->setShouldToolShowControls((bool) $query_result[IConfig::F_CONFIG]);
                    break;

                case IConfig::CNF_CUSTOM_FROM_EMAIL:
                    $config->setNotificationSenderAddress($query_result[IConfig::F_CONFIG]);
                    break;

                case IConfig::CNF_MAILING_BLACKLIST:
                    $config->setMailingBlacklist(
                        array_map('intval', $this->stringToArray($query_result[IConfig::F_CONFIG]))
                    );
                    break;

                case IConfig::CNF_FORCE_MAIL_FORWARDING:
                    $config->setMailForwardingForced((bool) $query_result[IConfig::F_CONFIG]);
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
        $this->updateConfig(IConfig::CNF_ROLE_MANAGE_ROUTINES, $this->arrayToString($config->getManageRoutineRoles()));
        $this->updateConfig(IConfig::CNF_ROLE_MANAGE_ASSIGNMENTS, $this->arrayToString($config->getManageAssignmentRoles()));
        $this->updateConfig(IConfig::CNF_TOOL_IS_ENABLED, (string) $config->isToolEnabled());
        $this->updateConfig(IConfig::CNF_TOOL_SHOW_ROUTINES, (string) $config->shouldToolShowRoutines());
        $this->updateConfig(IConfig::CNF_TOOL_SHOW_CONTROLS, (string) $config->shouldToolShowControls());
        $this->updateConfig(IConfig::CNF_CUSTOM_FROM_EMAIL, $config->getNotificationSenderAddress());
        $this->updateConfig(IConfig::CNF_MAILING_BLACKLIST, $this->arrayToString($config->getMailingBlacklist()));
        $this->updateConfig(IConfig::CNF_FORCE_MAIL_FORWARDING, (string) $config->isMailForwardingForced());

        return $config;
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

    /**
     * @todo: this could be improved with json_decode.
     * @param string $array
     * @return array
     */
    protected function stringToArray(string $array) : array
    {
        if (!empty($array)) {
            return explode(self::ARRAY_STRING_SEPARATOR, $array);
        }

        return [];
    }

    /**
     * @todo: this could be improved with json_encode.
     * @param array $array
     * @return string
     */
    protected function arrayToString(array $array) : string
    {
        return implode(self::ARRAY_STRING_SEPARATOR, $array);
    }
}