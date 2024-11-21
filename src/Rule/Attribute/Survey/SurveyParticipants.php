<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Survey;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SurveyParticipants extends SurveyAttribute
{
    /**
     * @inheritDoc
     */
    public function getComparableValueTypes(): array
    {
        return [
            self::COMPARABLE_VALUE_TYPE_INT,
            self::COMPARABLE_VALUE_TYPE_BOOL,
            self::COMPARABLE_VALUE_TYPE_STRING,
        ];
    }

    /**
     * @inheritDoc
     * @return bool|int|string|null
     */
    public function getComparableValue(string $type)
    {
        $participants = ($this->survey->get360Mode()) ?
            $this->survey->getAppraiseesData() :
            $this->survey->getSurveyParticipants(null, false, true);

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return count($participants);
            case self::COMPARABLE_VALUE_TYPE_BOOL:
                return !empty($participants);
            case self::COMPARABLE_VALUE_TYPE_STRING:
                return (string) count($participants);

            default:
                return null;
        }
    }
}
