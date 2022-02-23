<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Config;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IConfigAr
{
    /**
     * @var string config determines whether a user can manage routines (and rules).
     */
    public const CNF_GLOBAL_ROLES = 'cnf_global_roles';

    /**
     * @var string config determines whether objects that  match a routines rule-set are
     *             moved to the bin first, or are removed entirely.
     */
    public const CNF_MOVE_TO_BIN = 'cnf_move_to_bin';

    /**
     * @var string config determines whether routines can be added by the tool provider
     *             displayed in the repository context.
     */
    public const CNF_CREATE_ROUTINES = 'cnf_create_routines_repository';

    /**
     * @var string config determines whether active routines are shown in the tool provider
     *             displayed in the repository context's current object.
     */
    public const CNF_SHOW_ROUTINES = 'cnf_show_routines_repository';

    /**
     * @return string
     */
    public function getIdentifier() : string;

    /**
     * @param string $identifier
     * @return IConfigAr
     */
    public function setIdentifier(string $identifier) : IConfigAr;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     * @return IConfigAr
     */
    public function setValue($value) : IConfigAr;
}