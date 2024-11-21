<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObject;
use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilSrAbstractRoutinePreviewGenerator
{
    /**
     * @param AffectedObjectProvider $affected_object_provider
     */
    public function __construct(protected AffectedObjectProvider $affected_object_provider)
    {
    }

    /**
     * @return Generator|AffectedObject[]
     */
    protected function getDeletableItems(): Generator
    {
        return $this->affected_object_provider->getAffectedObjects();
    }
}
