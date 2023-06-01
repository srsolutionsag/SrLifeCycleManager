<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
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
     * @var AffectedObjectProvider
     */
    protected $affected_object_provider;

    /**
     * @param AffectedObjectProvider $object_provider
     */
    public function __construct(AffectedObjectProvider $object_provider)
    {
        $this->affected_object_provider = $object_provider;
    }

    /**
     * @return Generator|\srag\Plugins\SrLifeCycleManager\Object\AffectedObject[]
     */
    protected function getDeletableItems()
    {
        return $this->affected_object_provider->getAffectedObjects();
    }
}
