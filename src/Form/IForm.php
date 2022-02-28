<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace srag\Plugins\_SrLifeCycleManager\Form;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IForm
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
     * Returns the rendered form HTML string.
     *
     * @return string
     */
    public function render() : string;
}