<?php

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
