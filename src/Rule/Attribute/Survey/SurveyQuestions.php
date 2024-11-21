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
class SurveyQuestions extends SurveyAttribute
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
        $questions = $this->survey->getSurveyQuestions();

        switch ($type) {
            case self::COMPARABLE_VALUE_TYPE_INT:
                return count($questions);
            case self::COMPARABLE_VALUE_TYPE_BOOL:
                return !empty($questions);
            case self::COMPARABLE_VALUE_TYPE_STRING:
                return (string) count($questions);

            default:
                return null;
        }
    }
}
