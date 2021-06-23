<#1>
<?php
$fields = array(
    'identifier' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '250',
    ),
    'value' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '4000',
    ),
);
/**
 * @var $ilDB ilDBInterface
 */
if (! $ilDB->tableExists('srlcm_config')) {
    $ilDB->createTable('srlcm_config', $fields);
    $ilDB->addPrimaryKey('srlcm_config', array( 'identifier' ));
}
?>
<#2>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'lhs_type' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '255',
    ),
    'lhs_value' => array(
        'notnull' => '1',
        'type' => 'clob',
    ),
    'operator' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '20',
    ),
    'rhs_type' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '255',
    ),
    'rhs_value' => array(
        'notnull' => '1',
        'type' => 'clob',
    ),
);
if (! $ilDB->tableExists('srlcm_rule')) {
    $ilDB->createTable('srlcm_rule', $fields);
    $ilDB->addPrimaryKey('srlcm_rule', array( 'id' ));

    if (! $ilDB->sequenceExists('srlcm_rule')) {
        $ilDB->createSequence('srlcm_rule');
    }
}
?>
<#3>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'message' => array(
        'notnull' => '1',
        'type' => 'clob',
    ),
);
if (! $ilDB->tableExists('srlcm_msg')) {
    $ilDB->createTable('srlcm_msg', $fields);
    $ilDB->addPrimaryKey('srlcm_msg', array( 'id' ));
    if (! $ilDB->sequenceExists('srlcm_msg')) {
        $ilDB->createSequence('srlcm_msg');
    }
}
?>
<#4>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '8',
	),
	'ref_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '8',
	),
	'active' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',
	),
	'origin_type' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',
	),
	'owner_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '8',
	),
	'creation_date' => array(
		'notnull' => '1',
		'type' => 'date',
	),
	'opt_out_possible' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',
	),
	'elongation_possible' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',
	),
	'elongation_days' => array(
		'type' => 'integer',
		'length' => '8',
	),
);
if (! $ilDB->tableExists('srlcm_routine')) {
	$ilDB->createTable('srlcm_routine', $fields);
	$ilDB->addPrimaryKey('srlcm_routine', array( 'id' ));
	if (! $ilDB->sequenceExists('srlcm_routine')) {
		$ilDB->createSequence('srlcm_routine');
	}
}
?>
<#5>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'routine_id' => array(
        'type' => 'integer',
        'length' => '8',
    ),
    'rule_id' => array(
        'type' => 'integer',
        'length' => '8',
    ),

);
if (! $ilDB->tableExists('srlcm_routine_rule')) {
    $ilDB->createTable('srlcm_routine_rule', $fields);
    $ilDB->addPrimaryKey('srlcm_routine_rule', array( 'id' ));
    if (! $ilDB->sequenceExists('srlcm_routine_rule')) {
        $ilDB->createSequence('srlcm_routine_rule');
    }
}
?>
<#6>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'routine_id' => array(
        'type' => 'integer',
        'length' => '8',
    ),
    'notification_id' => array(
        'type' => 'integer',
        'length' => '8',
    ),
    'days_before_submission' => array(
        'type' => 'integer',
        'length' => '8',
    ),
);
if (! $ilDB->tableExists('srlcm_routine_msg')) {
    $ilDB->createTable('srlcm_routine_msg', $fields);
    $ilDB->addPrimaryKey('srlcm_routine_msg', array( 'id' ));
    if (! $ilDB->sequenceExists('srlcm_routine_msg')) {
        $ilDB->createSequence('srlcm_routine_msg');
    }
}
?>
<#7>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'whitelist_type' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '1',
    ),
    'routine_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'ref_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',
    ),
    'active_until' => array(
        'type' => 'date',
    ),

);
if (! $ilDB->tableExists('srlcm_routine_w_list')) {
    $ilDB->createTable('srlcm_routine_w_list', $fields);
    $ilDB->addPrimaryKey('srlcm_routine_w_list', array( 'id' ));
    if (! $ilDB->sequenceExists('srlcm_routine_w_list')) {
        $ilDB->createSequence('srlcm_routine_w_list');
    }
}
?>