<?php

trait CRM_HRLeaveAndAbsences_ApiHelpersTrait {

  private function callAPI($entity, $action, $params, $checkPermissions = true) {
    $params['check_permissions'] = $checkPermissions;
    return civicrm_api3($entity, $action, $params);
  }

  private function callAPIWithCheckPermissionsOff($entity, $action, $params) {
    return $this->callAPI($entity, $action, $params, false);
  }
}
