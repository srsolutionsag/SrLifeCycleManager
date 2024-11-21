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
class SurveyResults extends SurveyAttribute
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
        $is_360_survey = $this->survey->get360Mode();
        $result_count = 0;
        foreach ($this->getParticipants() as $participant_data) {
            if (($is_360_survey) ?
                $this->isAppraiseeFinished($participant_data) :
                $this->isParticipantFinished($participant_data)
            ) {
                $result_count++;
            }
        }

        return match ($type) {
            self::COMPARABLE_VALUE_TYPE_INT => $result_count,
            self::COMPARABLE_VALUE_TYPE_BOOL => 0 < $result_count,
            self::COMPARABLE_VALUE_TYPE_STRING => (string) $result_count,
            default => null,
        };
    }

    /**
     * Returns true if the appraisee closed the survey and all registered
     * raters have submitted their result.
     */
    protected function isAppraiseeFinished(array $data): bool
    {
        if (null === $data['closed']) {
            return false;
        }

        $rater_x_of_y_array = explode('/', (string) $data['finished']);

        // unexpected data was supplied if there are not exactly two
        // array entries (for x and y of a string 'x/y').
        if (2 === count($rater_x_of_y_array)) {
            // if x and y are equal, the appraisee is considered finished.
            // note that this is also true if '0/0'.
            return ($rater_x_of_y_array[0] === $rater_x_of_y_array[1]);
        }

        return false;
    }

    protected function isParticipantFinished(array $data): bool
    {
        return (bool) $data['finished'];
    }
}
