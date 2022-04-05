<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7aa5d9bc8a6e2685daf4aef313101773
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'srag\\Plugins\\SrLifeCycleManager\\Tests\\' => 38,
            'srag\\Plugins\\SrLifeCycleManager\\' => 32,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'srag\\Plugins\\SrLifeCycleManager\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'srag\\Plugins\\SrLifeCycleManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'ilSrAbstractCronJob' => __DIR__ . '/../..' . '/classes/Cron/class.ilSrAbstractCronJob.php',
        'ilSrAbstractGUI' => __DIR__ . '/../..' . '/classes/Util/class.ilSrAbstractGUI.php',
        'ilSrAbstractTable' => __DIR__ . '/../..' . '/classes/Util/class.ilSrAbstractTable.php',
        'ilSrAccessHandler' => __DIR__ . '/../..' . '/classes/Util/class.ilSrAccessHandler.php',
        'ilSrCleanUpCronJob' => __DIR__ . '/../..' . '/classes/Cron/class.ilSrCleanUpCronJob.php',
        'ilSrConfigGUI' => __DIR__ . '/../..' . '/classes/Config/class.ilSrConfigGUI.php',
        'ilSrConfigRepository' => __DIR__ . '/../..' . '/classes/Config/class.ilSrConfigRepository.php',
        'ilSrCronJobFactory' => __DIR__ . '/../..' . '/classes/Cron/class.ilSrCronJobFactory.php',
        'ilSrDryRoutineCronJob' => __DIR__ . '/../..' . '/classes/Cron/class.ilSrDryRoutineCronJob.php',
        'ilSrLifeCycleManagerConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerConfigGUI.php',
        'ilSrLifeCycleManagerDispatcher' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerDispatcher.php',
        'ilSrLifeCycleManagerPlugin' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerPlugin.php',
        'ilSrLifeCycleManagerRepository' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerRepository.php',
        'ilSrMenuProvider' => __DIR__ . '/../..' . '/classes/Provider/class.ilSrMenuProvider.php',
        'ilSrNotificationGUI' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationGUI.php',
        'ilSrNotificationRepository' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationRepository.php',
        'ilSrNotificationSender' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationSender.php',
        'ilSrNotificationTable' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationTable.php',
        'ilSrRepositoryFactory' => __DIR__ . '/../..' . '/classes/Util/class.ilSrRepositoryFactory.php',
        'ilSrRepositoryHelper' => __DIR__ . '/../..' . '/classes/Util/trait.ilSrRepositoryHelper.php',
        'ilSrRoutineAssignmentGUI' => __DIR__ . '/../..' . '/classes/Assignment/class.ilSrRoutineAssignmentGUI.php',
        'ilSrRoutineAssignmentRepository' => __DIR__ . '/../..' . '/classes/Assignment/class.ilSrRoutineAssignmentRepository.php',
        'ilSrRoutineAssignmentTable' => __DIR__ . '/../..' . '/classes/Assignment/class.ilSrRoutineAssignmentTable.php',
        'ilSrRoutineCronJob' => __DIR__ . '/../..' . '/classes/Cron/class.ilSrRoutineCronJob.php',
        'ilSrRoutineGUI' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineGUI.php',
        'ilSrRoutineList' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineList.php',
        'ilSrRoutineRepository' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineRepository.php',
        'ilSrRoutineTable' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineTable.php',
        'ilSrRuleGUI' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRuleGUI.php',
        'ilSrRuleRepository' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRuleRepository.php',
        'ilSrRuleTable' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRuleTable.php',
        'ilSrTabManager' => __DIR__ . '/../..' . '/classes/Util/class.ilSrTabManager.php',
        'ilSrToolProvider' => __DIR__ . '/../..' . '/classes/Provider/class.ilSrToolProvider.php',
        'ilSrToolbarManager' => __DIR__ . '/../..' . '/classes/Util/class.ilSrToolbarManager.php',
        'ilSrWhitelistGUI' => __DIR__ . '/../..' . '/classes/Whitelist/class.ilSrWhitelistGUI.php',
        'ilSrWhitelistRepository' => __DIR__ . '/../..' . '/classes/Whitelist/class.ilSrWhitelistRepository.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7aa5d9bc8a6e2685daf4aef313101773::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7aa5d9bc8a6e2685daf4aef313101773::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7aa5d9bc8a6e2685daf4aef313101773::$classMap;

        }, null, ClassLoader::class);
    }
}
