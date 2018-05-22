<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestFieldsVisibility implements API_Wrapper {

  /**
   * @var array
   */
  protected $apiActions = [
    'getfull' => ['handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions'],
    'get' => ['handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions'],
    'getbreakdown' => ['handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissions'],
  ];

  /**
   * @param $apiRequest
   * @return mixed
   */
  public function fromApiInput($apiRequest) {

    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $handler = $this->getHandler($apiRequest);
      $handler->process($result);
    }

    return $result;
  }

  /**
   * @param $apiRequest
   * @return mixed
   */
  private function getHandler($apiRequest) {
    $handler = $this->apiActions[$apiRequest['action']]['handler'];
    $leaveRequestRights = new LeaveRequestRightsService(new LeaveManagerService());

    return new $handler($apiRequest, $leaveRequestRights);
  }

  /**
   * @param $apiRequest
   * @return bool
   */
  private function canHandleTheRequest($apiRequest) {
    $isTargetAction = array_key_exists($apiRequest['action'], $this->apiActions);
    return $apiRequest['entity'] === 'LeaveRequest' && $isTargetAction;
  }
}
