<?php

return [
  [
    'name' => 'Scheduled Job: Create expiry records for expired LeaveBalanceChanges',
    'entity' => 'Job',
    'params' => [
      'version'       => 3,
      'api_entity'    => 'LeaveBalanceChange',
      'api_action'    => 'create_expiry_records',
      'domain_id'     => CRM_Core_Config::domainID(),
      'run_frequency' => 'Daily',
      'parameters'    => NULL,
      'name'          => 'Create expiry records for expired LeaveBalanceChanges',
      'description'   => 'Checks the number of days taken as leave during the LeaveBalanceChange period and add a negative balance change to expire the number of days not used',
    ],
  ],
  [
    'name' => 'Scheduled Job: Process Public Holiday Leave Requests Updates',
    'entity' => 'Job',
    'params' => [
      'version'       => 3,
      'api_entity'    => 'PublicHoliday',
      'api_action'    => 'process_public_holiday_leave_request_updates_queue',
      'domain_id'     => CRM_Core_Config::domainID(),
      'run_frequency' => 'Always',
      'parameters'    => NULL,
      'name'          => 'Process Public Holiday Leave Requests Updates',
      'description'   => 'Process all the tasks on the "Public Holiday Leave Requests Update" queue',
    ],
  ],
];
