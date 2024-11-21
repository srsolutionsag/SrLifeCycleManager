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
     * @return bool|int|string|null
     */
    public function getComparableValue(string $type): int|bool|string|null
    {
        $questions = $this->survey->getSurveyQuestions();

        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_INT => count($questions),
            self::COMPARABLE_VALUE_TYPE_BOOL => !empty($questions),
            self::COMPARABLE_VALUE_TYPE_STRING => (string) count($questions),
            default => null,
        };
    }
}
