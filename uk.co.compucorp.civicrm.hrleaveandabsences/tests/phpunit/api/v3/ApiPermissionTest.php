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
  public function testAPIPermissions($entity, $action, $permission = 'access AJAX API') {
    $contactID = 1;
    $this->registerCurrentLoggedInContactInSession($contactID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->setExpectedException('CiviCRM_API3_Exception', "API permission check failed for {$entity}/{$action} call; insufficient permission: require {$permission}");

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
      ['LeaveRequest', 'getcomment'],
      ['LeaveRequest', 'addcomment'],
      ['LeaveRequest', 'deletecomment'],
      ['LeaveRequest', 'getattachments'],
      ['LeaveRequest', 'deleteattachment'],
      ['LeaveRequest', 'delete'],
      ['LeaveRequest', 'getbreakdown'],
      ['WorkPattern', 'getcalendar'],
      ['AbsenceType', 'get'],
      ['AbsencePeriod', 'get'],
      ['OptionGroup', 'get'],
      ['OptionValue', 'get'],
      ['LeavePeriodEntitlement', 'get'],
      ['LeavePeriodEntitlement', 'getleavebalances', 'manage leave and absences in ssp'],
      ['PublicHoliday', 'get'],
      ['Comment', 'get'],
      ['Comment', 'create'],
      ['Comment', 'delete'],
      ['Contact', 'getleavemanagees'],
    ];
  }
}


