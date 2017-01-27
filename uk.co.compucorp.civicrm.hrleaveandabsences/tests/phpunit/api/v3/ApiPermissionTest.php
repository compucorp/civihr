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

    $entityName = $this->formatSnakeToCamelCase($entity);
    $this->setExpectedException('CiviCRM_API3_Exception', "API permission check failed for {$entityName}/{$action} call; insufficient permission: require access AJAX API");

    $payload = ['check_permissions' => true];

    if($action == 'update') {
      $payload['id'] = 1;
    }

    civicrm_api3($entity, $action, $payload);
  }

  public function apiPermissionsDataProvider() {
    return [
      ['leave_request', 'isvalid'],
      ['leave_request', 'getfull'],
      ['leave_request', 'ismanagedby'],
      ['leave_request', 'update'],
      ['leave_request', 'create'],
      ['leave_request', 'calculatebalancechange'],
      ['leave_request', 'getbalancechangebyabsencetype'],
      ['work_pattern', 'getcalendar'],
      ['absence_type', 'get'],
      ['absence_period', 'get'],
      ['option_group', 'get'],
      ['option_value', 'get'],
      ['leave_period_entitlement', 'get'],
      ['public_holiday', 'get']
    ];
  }

  private function formatSnakeToCamelCase($text) {
    $text_array = explode('_', $text);
    $camelCaseText = '';
    foreach ($text_array as $value){
      $camelCaseText .= ucfirst($value);
    }

    return $camelCaseText;
  }
}


