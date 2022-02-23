<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;
use srag\Plugins\SrLifeCycleManager\Config\IConfigAr;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;
use srag\Plugins\SrLifeCycleManager\Config\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrConfigRepository implements IConfigRepository
{
    /**
     * @inheritDoc
     */
    public function get() : IConfig
    {
        /** @var $ar_config_array IConfigAr[] */
        $ar_config_array = ilSrConfig::get();
        $config_object = new Config();
        foreach ($ar_config_array as $ar_config) {
            // switch through identifier to use the correct config setter.
            switch ($ar_config->getIdentifier()) {
                case IConfigAr::CNF_GLOBAL_ROLES:
                    $config_object->setPrivilegedRoles($ar_config->getValue());
                    break;
                case IConfigAr::CNF_MOVE_TO_BIN:
                    $config_object->setMoveToBin((bool) $ar_config->getValue());
                    break;
                case IConfigAr::CNF_SHOW_ROUTINES:
                    $config_object->setShowRoutinesInRepository((bool) $ar_config->getValue());
                    break;
                case IConfigAr::CNF_CREATE_ROUTINES:
                    $config_object->setCreateRoutinesInRepository((bool) $ar_config->getValue());
                    break;
            }
        }

        return $config_object;
    }

    /**
     * @inheritDoc
     */
    public function store(array $post_data) : IConfig
    {
        foreach ($post_data as $identifier => $value) {
            // try to find an existing database entry for current
            // $identifier or create a new instance.
            $config = ilSrConfig::find($identifier) ?? new ilSrConfig();
            $config
                // this may be redundant, but more performant than if-else
                ->setIdentifier($identifier)
                ->setValue($value)
                ->store()
            ;
        }

        return $this->get();
    }
}