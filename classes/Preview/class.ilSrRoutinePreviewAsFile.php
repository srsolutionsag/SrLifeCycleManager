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
class ilSrRoutinePreviewAsFile extends ilSrAbstractRoutinePreviewGenerator
{
    
    /**
     * @return string
     */
    public function getTxtFileContent() : string
    {
        $items = [];
        foreach ($this->getDeletableItems() as $item) {
            $instance = $item->getInstance();
            
            $content = sprintf(
                "%s | %s\n",
                $instance->getTitle(),
                ilLink::_getLink($instance->getRefId())
            );
            
            foreach ($item->getAffectingRoutines() as $routine) {
                $content .= sprintf(
                    " - Routine: %s\n",
                    $routine->getTitle()
                );
            }
            
            $content .= "\n";
            
            $items[] = $content;
        }
        
        return implode("\n", $items);
    }
}
