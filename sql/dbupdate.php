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
        'ref_id' => [
            'notnull' => '1',
            'length'  => '8',
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