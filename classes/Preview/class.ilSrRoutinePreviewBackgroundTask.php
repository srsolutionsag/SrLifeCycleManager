<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Object\AffectedObjectProvider;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\Filesystem\Filesystems;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewBackgroundTask extends AbstractJob
{
    protected AffectedObjectProvider $affected_object_provider;

    protected Filesystems $file_system;

    public function __construct()
    {
        global $DIC;

        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilSrLifeCycleManagerPlugin */
        $plugin = $component_factory->getPlugin(ilSrLifeCycleManagerPlugin::PLUGIN_ID);

        $this->affected_object_provider = $plugin->getContainer()->getAffectedObjectProvider();
        $this->file_system = $DIC->filesystem();
    }

    /**
     * @TODO: test this cron job, removed ilUtil::ilTempnam() by using the filesystem service.
     */
    public function run(array $input, Observer $observer): Value
    {
        $observer->notifyPercentage($this, 10);
        $path = new StringValue();

        $preview_builder = new ilSrRoutinePreviewAsFile($this->affected_object_provider);

        $observer->notifyPercentage($this, 30);
        $content = $preview_builder->getTxtFileContent();

        $observer->notifyPercentage($this, 40);
        $tmp_file = str_replace(".", "", uniqid("tmp", true));
        $this->file_system->temp()->put($tmp_file, $content);

        $observer->notifyPercentage($this, 50);
        $observer->notifyPercentage($this, 60);

        $path->setValue($tmp_file);

        return $path;
    }

    public function isStateless(): bool
    {
        return false;
    }

    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 3600;
    }

    public function getInputTypes(): array
    {
        return [];
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }
}
