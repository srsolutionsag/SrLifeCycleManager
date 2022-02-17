<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit78b368899f4397655d4e05c324fa6b84
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'srag\\Plugins\\SrLifeCycleManager\\' => 32,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'srag\\Plugins\\SrLifeCycleManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'ilSrAbstractForm' => __DIR__ . '/../..' . '/classes/Abstract/class.ilSrAbstractForm.php',
        'ilSrAbstractGUI' => __DIR__ . '/../..' . '/classes/Abstract/class.ilSrAbstractGUI.php',
        'ilSrAbstractTable' => __DIR__ . '/../..' . '/classes/Abstract/class.ilSrAbstractTable.php',
        'ilSrAccess' => __DIR__ . '/../..' . '/classes/Access/class.ilSrAccess.php',
        'ilSrConfig' => __DIR__ . '/../..' . '/classes/Config/class.ilSrConfig.php',
        'ilSrConfigForm' => __DIR__ . '/../..' . '/classes/Config/Form/class.ilSrConfigForm.php',
        'ilSrConfigGUI' => __DIR__ . '/../..' . '/classes/Config/class.ilSrConfigGUI.php',
        'ilSrLifeCycleManagerConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerConfigGUI.php',
        'ilSrLifeCycleManagerDispatcher' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerDispatcher.php',
        'ilSrLifeCycleManagerPlugin' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerPlugin.php',
        'ilSrLifeCycleManagerRepository' => __DIR__ . '/../..' . '/classes/class.ilSrLifeCycleManagerRepository.php',
        'ilSrMenuProvider' => __DIR__ . '/../..' . '/classes/Provider/class.ilSrMenuProvider.php',
        'ilSrNotification' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotification.php',
        'ilSrNotificationForm' => __DIR__ . '/../..' . '/classes/Notification/Form/class.ilSrNotificationForm.php',
        'ilSrNotificationGUI' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationGUI.php',
        'ilSrNotificationRepository' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrNotificationRepository.php',
        'ilSrNotificationTable' => __DIR__ . '/../..' . '/classes/Notification/Table/class.ilSrNotificationTable.php',
        'ilSrRoutine' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutine.php',
        'ilSrRoutineForm' => __DIR__ . '/../..' . '/classes/Routine/Form/class.ilSrRoutineForm.php',
        'ilSrRoutineGUI' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineGUI.php',
        'ilSrRoutineNotification' => __DIR__ . '/../..' . '/classes/Notification/class.ilSrRoutineNotification.php',
        'ilSrRoutineRepository' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineRepository.php',
        'ilSrRoutineRule' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRoutineRule.php',
        'ilSrRoutineTable' => __DIR__ . '/../..' . '/classes/Routine/Table/class.ilSrRoutineTable.php',
        'ilSrRoutineWhitelist' => __DIR__ . '/../..' . '/classes/Routine/class.ilSrRoutineWhitelistEntry.php',
        'ilSrRule' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRule.php',
        'ilSrRuleForm' => __DIR__ . '/../..' . '/classes/Rule/Form/class.ilSrRuleForm.php',
        'ilSrRuleGUI' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRuleGUI.php',
        'ilSrRuleRepository' => __DIR__ . '/../..' . '/classes/Rule/class.ilSrRuleRepository.php',
        'ilSrRuleTable' => __DIR__ . '/../..' . '/classes/Rule/Table/class.ilSrRuleTable.php',
        'ilSrToolProvider' => __DIR__ . '/../..' . '/classes/Provider/class.ilSrToolProvider.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit78b368899f4397655d4e05c324fa6b84::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit78b368899f4397655d4e05c324fa6b84::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit78b368899f4397655d4e05c324fa6b84::$classMap;

        }, null, ClassLoader::class);
    }
}
