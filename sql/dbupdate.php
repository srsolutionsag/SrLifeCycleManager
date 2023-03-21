<#1>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_configuration';
    $columns = [
        'identifier' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
        'configuration' => [
            'notnull' => '1',
            'length'  => '4000',
            'type'    => 'text',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'identifier',
        ]);
    }
?>
<#2>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_routine';
    $columns = [
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'usr_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'origin_type' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'routine_type' => [
            'notnull' => '1',
            'length'  => '20',
            'type'    => 'text',
        ],
        'has_opt_out' => [
            'notnull' => '1',
            'length'  => '1',
            'type'    => 'integer',
        ],
        'title' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
        'elongation' => [
            'length'  => '8',
            'type'    => 'integer',
        ],
        'creation_date' => [
            'notnull' => '1',
            'type'    => 'date',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'routine_id',
        ]);
    }

    if (!$ilDB->sequenceExists($table_name)) {
        $ilDB->createSequence($table_name);
    }
?>
<#3>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_notification';
    $columns = [
        'notification_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'title' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
        'content' => [
            'notnull' => '1',
            'length'  => '4000',
            'type'    => 'text',
        ],
        'days_before_submission' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'notification_id',
        ]);
    }

    if (!$ilDB->sequenceExists($table_name)) {
        $ilDB->createSequence($table_name);
    }
?>
<#4>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_rule';
    $columns = [
        'rule_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'lhs_type' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
        'lhs_value' => [
            'notnull' => '1',
            'length'  => '4000',
            'type'    => 'text',
        ],
        'rhs_type' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
        'rhs_value' => [
            'notnull' => '1',
            'length'  => '4000',
            'type'    => 'text',
        ],
        'operator' => [
            'notnull' => '1',
            'length'  => '254',
            'type'    => 'text',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'rule_id',
        ]);
    }

    if (!$ilDB->sequenceExists($table_name)) {
        $ilDB->createSequence($table_name);
    }
?>
<#5>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_routine_rule';
    $columns = [
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'rule_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'routine_id',
            'rule_id',
        ]);
    }
?>
<#6>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_whitelist';
    $columns = [
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'ref_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'usr_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'is_opt_out' => [
            'notnull' => '1',
            'length'  => '1',
            'type'    => 'integer',
        ],
        'elongation' => [
            'length'  => '8',
            'type'    => 'integer',
        ],
        'date' => [
            'notnull' => '1',
            'type'    => 'date',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'routine_id',
            'ref_id',
        ]);
    }
?>
<#7>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_notified_objects';
    $columns = [
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'notification_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'ref_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'date' => [
            'notnull' => '1',
            'type'    => 'date',
        ],
    ];

    if (!$ilDB->tableExists($table_name)) {
        $ilDB->createTable($table_name, $columns);
        $ilDB->addPrimaryKey($table_name, [
            'routine_id',
            'notification_id',
            'ref_id',
        ]);
    }
?>
<#8>
<?php
    /** @var $ilDB ilDBInterface */
    $table_name = 'srlcm_configuration';
    if ($ilDB->tableExists($table_name)) {
        $ilDB->insert($table_name, [
            'identifier'    => ['text', 'cnf_role_manage_routines'],
            'configuration' => ['text', ''],
        ]);

        $ilDB->insert($table_name, [
            'identifier'    => ['text', 'cnf_role_manage_assignments'],
            'configuration' => ['text', ''],
        ]);

        $ilDB->insert($table_name, [
            'identifier'    => ['text', 'cnf_tool_is_enabled'],
            'configuration' => ['text', '0'],
        ]);

        $ilDB->insert($table_name, [
            'identifier'    => ['text', 'cnf_tool_show_routines'],
            'configuration' => ['text', '0'],
        ]);

        $ilDB->insert($table_name, [
            'identifier'    => ['text', 'cnf_tool_show_controls'],
            'configuration' => ['text', '0'],
        ]);
    }
?>
<#9>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_assigned_routine';
if (!$ilDB->tableExists($table_name)) {
    $columns = [
        'routine_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'ref_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'usr_id' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'is_active' => [
            'notnull' => '1',
            'length'  => '8',
            'type'    => 'integer',
        ],
        'is_recursive' => [
            'notnull' => '1',
            'length'  => '1',
            'type'    => 'integer',
        ],
    ];

    $ilDB->createTable($table_name, $columns);
    $ilDB->addPrimaryKey($table_name, [
        'routine_id',
        'ref_id',
    ]);
}
?>
<#10>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_configuration';

if ($ilDB->tableExists($table_name)) {
    $ilDB->insert($table_name, [
        'identifier' => ['text', 'cnf_custom_from_email'],
        'configuration' => ['text', ''],
    ]);
}
?>
<#11>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_configuration';

if ($ilDB->tableExists($table_name)) {
    $ilDB->insert($table_name, [
        'identifier' => ['text', 'cnf_mailing_whitelist'],
        'configuration' => ['text', ''],
    ]);
}
?>
<#12>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_configuration';

if ($ilDB->tableExists($table_name)) {
    $ilDB->update($table_name, [
        'identifier' => ['text', 'cnf_mailing_blacklist']
    ], [
        'identifier' => ['text', 'cnf_mailing_whitelist']
    ]);
}
?>
<#13>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_reminder';
$columns = [
    'notification_id' => [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ],
    'days_before_deletion' => [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ],
];

if (!$ilDB->tableExists($table_name)) {
    $ilDB->createTable($table_name, $columns);
    $ilDB->addPrimaryKey($table_name, [
        'notification_id',
    ]);
}
?>
<#14>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_notification';

if ($ilDB->tableColumnExists($table_name, 'days_before_submission')) {
    $ilDB->manipulate("
        INSERT INTO srlcm_reminder (notification_id, days_before_deletion)
            SELECT notification_id, days_before_submission FROM srlcm_notification
        ;
    ");

    $ilDB->dropTableColumn($table_name, 'days_before_submission');
}
?>
<#15>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_confirmation';
$columns = [
    'notification_id' => [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ],
    'event' => [
        'notnull' => '1',
        'length'  => '254',
        'type'    => 'text',
    ],
];

if (!$ilDB->tableExists($table_name)) {
    $ilDB->createTable($table_name, $columns);
    $ilDB->addPrimaryKey($table_name, [
        'notification_id',
    ]);
}
?>
<#16>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_whitelist';
$legacy_column_name = 'elongation';
$column_name = 'expiry_date';

if (!$ilDB->tableColumnExists($table_name, $column_name)) {
    $ilDB->addTableColumn($table_name, $column_name, [
        'notnull' => '0',
        'type'    => 'date',
    ]);

    // migrate existing elongations to an expiry date by calculating it
    // like it was done up till v1.7.0.
    $ilDB->manipulate("
        UPDATE srlcm_whitelist
            SET expiry_date = DATE_ADD(date, INTERVAL elongation DAY)
            WHERE elongation IS NOT NULL
        ;
    ");

    // remove legacy column after migration.
    $ilDB->dropTableColumn($table_name, $legacy_column_name);
}
?>
<#17>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_tokens';
$columns = [
    'routine_id' => [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ],
    'ref_id' => [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ],
    'event' => [
        'notnull' => '1',
        'length'  => '254',
        'type'    => 'text',
    ],
    'token' => [
        'notnull' => '1',
        'length'  => '64',
        'type'    => 'text',
    ],
];

if (!$ilDB->tableExists($table_name)) {
    $ilDB->createTable($table_name, $columns);
    $ilDB->addPrimaryKey($table_name, [
        'routine_id',
        'ref_id',
        'event',
        'token',
    ]);
}
?>
<#18>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_routine';
$column_name = 'elongation_cooldown';

if (!$ilDB->tableColumnExists($table_name, $column_name)) {
    $ilDB->addTableColumn($table_name, $column_name, [
        'notnull' => '1',
        'length'  => '8',
        'type'    => 'integer',
    ]);

    // set the default cooldown of all existing routines with elongations
    // to exactly one day.
    $ilDB->manipulate("
        UPDATE srlcm_routine SET elongation_cooldown = 1 WHERE elongation IS NOT NULL;
    ");
}
?>
<#19>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_configuration';

if ($ilDB->tableExists($table_name)) {
    $ilDB->insert($table_name, [
        'identifier' => ['text', 'cnf_force_mail_forwarding'],
        'configuration' => ['text', '0'],
    ]);
}
?>
<#20>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_routine';
$column_name = 'elongation_cooldown';

if ($ilDB->tableColumnExists($table_name, $column_name)) {
    $ilDB->modifyTableColumn($table_name, $column_name, [
        'notnull' => '0',
    ]);
}
?>
<#21>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_confirmation';
$column_name = 'event';

if ($ilDB->tableColumnExists($table_name, $column_name)) {
    $legacy_event_name_mapping = [
        'onPostpone' => 'routine_postpone',
        'onOptOut' => 'routine_opt_out',
        'onDelete' => 'routine_delete',
    ];

    foreach ($legacy_event_name_mapping as $legacy_event => $new_event) {
        $ilDB->update(
            $table_name,
            ['event' => ['text', $new_event]],
            ['event' => ['text', $legacy_event]]
        );
    }
}
?>
<#22>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_configuration';

if ($ilDB->tableExists($table_name)) {
    $ilDB->insert($table_name, [
        'identifier' => ['text', 'cnf_debug_mode'],
        'configuration' => ['text', '0'],
    ]);
}
?>
<#23>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_tokens';
$column_name = 'event';

if ($ilDB->tableColumnExists($table_name, $column_name)) {
    $legacy_event_name_mapping = [
        'onPostpone' => 'routine_postpone',
        'onOptOut' => 'routine_opt_out',
        'onDelete' => 'routine_delete',
    ];

    foreach ($legacy_event_name_mapping as $legacy_event => $new_event) {
        $ilDB->update(
            $table_name,
            ['event' => ['text', $new_event]],
            ['event' => ['text', $legacy_event]]
        );
    }
}
?>
<#24>
<?php
/** @var $ilDB ilDBInterface */
$table_name = 'srlcm_whitelist';
$column_name = 'date';

if ($ilDB->tableColumnExists($table_name, $column_name)) {
    $ilDB->modifyTableColumn($table_name, $column_name, [
        'notnull' => '0',
        'type' => 'date',
    ]);
}
?>
<#25>
<?php
/** @var $ilDB ilDBInterface */
$value_migration = new srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration\ValueMigration(
    $ilDB, 'srlcm_rule', 'lhs_value', 'rhs_value'
);

$type_migration = new srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration\TypeMigration(
    $ilDB,
    'srlcm_rule',
    'lhs_value',
    'lhs_type',
    'rhs_value',
    'rhs_type'
);

$value_migration->migrateAll(
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseMember',
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant\ParticipantAll'
);

$value_migration->migrateAll(
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupMember',
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Participant\ParticipantAll'
);

?>
<#26>
<?php
/** @var $ilDB ilDBInterface */
$new_age_attribute_value = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAge';
$new_creation_attribute_value = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectCreation';
$new_title_attribute_value = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectTitle';
$new_metadata_attribute_value = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectMetadata';
$new_taxonomy_attribute_value = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectTaxonomy';

$legacy_course_value_mapping = [
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAge' => $new_age_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseCreation' => $new_creation_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseTitle' => $new_title_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseMetadata' => $new_metadata_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseTaxonomy' => $new_taxonomy_attribute_value,
];

$legacy_group_value_mapping = [
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAge' => $new_age_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupCreation' => $new_creation_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupTitle' => $new_title_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupMetadata' => $new_metadata_attribute_value,
    'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupTaxonomy' => $new_taxonomy_attribute_value,
];

$new_attribute_type = 'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Object\ObjectAttribute';

$value_migration = new srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration\ValueMigration(
    $ilDB, 'srlcm_rule', 'lhs_type', 'rhs_type'
);

$type_migration = new srag\Plugins\SrLifeCycleManager\Rule\Attribute\Migration\TypeMigration(
    $ilDB,
    'srlcm_rule',
    'lhs_value',
    'lhs_type',
    'rhs_value',
    'rhs_type'
);

foreach ($legacy_course_value_mapping as $legacy_value => $new_value) {
    $value_migration->migrateAll($legacy_value, $new_value);
    $type_migration->migrateForValue(
        'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute',
        $new_attribute_type,
        $new_value
    );
}

foreach ($legacy_group_value_mapping as $legacy_value => $new_value) {
    $value_migration->migrateAll($legacy_value, $new_value);
    $type_migration->migrateForValue(
        'srag\Plugins\SrLifeCycleManager\Rule\Attribute\Group\GroupAttribute',
        $new_attribute_type,
        $new_value
    );
}
?>
