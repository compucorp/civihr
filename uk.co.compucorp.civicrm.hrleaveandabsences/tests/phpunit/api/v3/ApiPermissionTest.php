<?php

/**
 * Class api_v3_ApiPermissionTest
 *
 * @group headless
 */
class api_v3_ApiPermissionTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  /**
   * @dataProvider apiPermissionsDataProvider
   */
  public function testAPIPermissions($entity, $action) {
    $contactID = 1;
    $this->registerCurrentLoggedInContactInSession($contactID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->setExpectedException('CiviCRM_API3_Exception', "API permission check failed for {$entity}/{$action} call; insufficient permission: require access AJAX API");

    $payload = ['check_permissions' => true];

    if($action == 'update') {
      $payload['id'] = 1;
    }

    civicrm_api3($entity, $action, $payload);
  }

  public function apiPermissionsDataProvider() {
    return [
      ['LeaveRequest', 'get'],
      ['LeaveRequest', 'isvalid'],
      ['LeaveRequest', 'getfull'],
      ['LeaveRequest', 'ismanagedby'],
      ['LeaveRequest', 'update'],
      ['LeaveRequest', 'create'],
      ['LeaveRequest', 'calculatebalancechange'],
      ['LeaveRequest', 'getbalancechangebyabsencetype'],
      ['WorkPattern', 'getcalendar'],
      ['AbsenceType', 'get'],
      ['AbsencePeriod', 'get'],
      ['OptionGroup', 'get'],
      ['OptionValue', 'get'],
      ['LeavePeriodEntitlement', 'get'],
      ['PublicHoliday', 'get']
    ];
  }
}


