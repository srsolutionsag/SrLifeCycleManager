<?php

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
        $download_name = $input[0]; //directory name.
        $download_path = $input[1]; // zip job

        if ($user_selected_option->getValue() != self::OPTION_DOWNLOAD) {
            // delete zip file
            $filesystem = $DIC->filesystem()->temp();
            try {
                $path = LegacyPathHelper::createRelativePath($download_path->getValue());
            } catch (InvalidArgumentException $e) {
                $path = null;
            }
            if (!is_null($path) && $filesystem->has($path)) {
                $filesystem->deleteDir(dirname($path));
            }

            return $download_path;
        }

        $download_path = $download_path->getValue();
        ilFileDelivery::deliverFileAttached($download_name->getValue(), $download_path);

        return $download_path;
    }
}
