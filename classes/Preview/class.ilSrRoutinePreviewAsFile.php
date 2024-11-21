<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewAsFile extends ilSrAbstractRoutinePreviewGenerator
{
    public function getTxtFileContent(): string
    {
        $items = [];
        foreach ($this->getDeletableItems() as $item) {
            $instance = $item->getObject();

            $content = sprintf(
                "%s | %s\n",
                $instance->getTitle(),
                ilLink::_getLink($instance->getRefId())
            );

            $content .= sprintf(
                " - Routine: %s\n",
                $item->getRoutine()->getTitle()
            );

            $content .= "\n";

            $items[] = $content;
        }

        return implode("\n", $items);
    }
}
