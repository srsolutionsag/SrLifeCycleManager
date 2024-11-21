<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewBackgroundDownloadInteraction extends AbstractUserInteraction
{
    public const OPTION_DOWNLOAD = 'download';
    public const OPTION_CANCEL = 'cancel';

    public function getInputTypes(): array
    {
        return [
            new SingleType(StringValue::class),
            new SingleType(StringValue::class),
        ];
    }

    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    public function getOptions(array $input): array
    {
        return [
            new UserInteractionOption('download', self::OPTION_DOWNLOAD),
        ];
    }

    public function interaction(array $input, Option $user_selected_option, Bucket $bucket): Value
    {
        global $DIC;
        $download_name_value_object = $input[1] ?? null; // Name of the zip file
        $download_path_value_object = $input[0] ?? null; // Relative path to the zip file

        if (!$download_name_value_object instanceof \ILIAS\BackgroundTasks\Value || !$download_path_value_object instanceof \ILIAS\BackgroundTasks\Value) {
            return $download_path_value_object;
        }

        $filesystem = $DIC->filesystem()->temp();
        if ($user_selected_option->getValue() !== self::OPTION_DOWNLOAD) {
            // delete zip file
            try {
                $filesystem->deleteDir($download_path_value_object->getValue());
            } catch (Throwable $t) {
            }

            return $download_path_value_object;
        }

        $file_stream = $filesystem->readStream($download_path_value_object->getValue());
        $absolut_path = $file_stream->getMetadata()['uri'] ?? '';

        ilFileDelivery::deliverFileAttached(
            $absolut_path,
            $download_name_value_object->getValue()
        );

        return $download_path_value_object;
    }
}
