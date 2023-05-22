<?php

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutinePreviewBackgroundTask extends AbstractJob
{
    public function run(array $input, Observer $observer)
    {
        $path = new StringValue();
        
        $preview_builder = new ilSrRoutinePreviewAsFile(
            ilSrLifeCycleManagerPlugin::getInstance()->getContainer()->getDeletableObjectProvider()
        );
        
        $content = $preview_builder->getTxtFileContent();
        
        $tmpdir = ilUtil::ilTempnam();
        file_put_contents($tmpdir, $content);
        
        $path->setValue($tmpdir);
        
        return $path;
    }
    
    public function isStateless()
    {
        return false;
    }
    
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 3600;
    }
    
    public function getInputTypes()
    {
        return [];
    }
    
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }
    
}
