<?php declare(strict_types=1);

/* Copyright (c) 2022 Fabian Schmid <fabian@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObjectProvider;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractRoutinePreviewGenerator
{
    /**
     * @var DeletableObjectProvider
     */
    protected $object_provider;
    
    /**
     * @param DeletableObjectProvider $object_provider
     */
    public function __construct(
        DeletableObjectProvider $object_provider
    ) {
        $this->object_provider = $object_provider;
    }
    
    /**
     * @return Generator|\srag\Plugins\SrLifeCycleManager\Routine\Provider\DeletableObject[]
     */
    protected function getDeletableItems()
    {
        return $this->object_provider->getDeletableObjects();
    }
    
}
