<?php

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Notification\IRecipientRetriever;
use srag\Plugins\SrLifeCycleManager\Config\IConfig;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRecipientRetriever implements IRecipientRetriever
{
    /**
     * @inheritDoc
     */
    public function getRecipients(\ilObject $object): array
    {
        if ($object instanceof ilObjCourse || $object instanceof ilObjGroup) {
            return $this->getAdministrators($object->getMembersObject());
        }

        if ($object instanceof ilObjSurvey) {
            return [
                $object->getOwner(),
            ];
        }

        throw new LogicException("Could not gather recipients for object of type '{$object->getType()}'.");
    }

    /**
     * @return int[]
     */
    protected function getAdministrators(ilParticipants $participants): array
    {
        return array_map('intval', $participants->getAdmins());
    }
}
