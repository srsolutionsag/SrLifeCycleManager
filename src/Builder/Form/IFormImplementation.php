<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\SrLifeCycleManager\Builder\Form;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface IFormImplementation
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * This form describes how an ilias form implementation should look
 * like. The minimal requirement should be handling requests and
 * eventually rendering the generated form to the main template.
 */
interface IFormImplementation
{
    /**
     * Handles the form submission for the given request.
     *
     * The data is extracted from the given request and validated
     * by the derived classes method. If it's valid the data gets
     * processed.
     *
     * The boolean return value can be used to check whether the
     * form needs to be rendered (to show errors).
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function handleRequest(ServerRequestInterface $request) : bool;

    /**
     * Prints the current form to the global template.
     */
    public function printToGlobalTemplate() : void;
}