<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * Class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestFieldsVisibility
 */
class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestFieldsVisibility implements API_Wrapper {

  /**
   * @var array
   *   An array of API actions for the LeaveRequest Entity with properties for
   *   each action that determine which handler should handle which API and also
   *   if the contact_id should be among the fields to be returned for the API
   *   for the respective handler to work properly. Since the Identifier field used
   *   by some of the handler to process the field values to be hidden is the contact_id,
   *   There will be unreliable results if this field is absent in the results set.
   */
  protected $apiActions = [
    'getfull' => [
      'handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions',
      'request_requires_contact_id' => TRUE
    ],
    'get' => [
      'handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions',
      'request_requires_contact_id' => TRUE
    ],
    'getbreakdown' => [
      'handler' => 'CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissions'
    ],
  ];

  /**
   * @param $apiRequest
   * @return mixed
   */
  public function fromApiInput($apiRequest) {
    if ($this->canHandleApiInput($apiRequest)) {
      $this->setRequestReturnParameter($apiRequest);
    }

    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if ($this->canHandleAPiOutput($apiRequest)) {
      $handler = $this->getHandler($apiRequest);
      $handler->process($result);
    }

    return $result;
  }

  /**
   * Returns the handler for the API action.
   *
   * @param $apiRequest
   *
   * @return mixed
   */
  private function getHandler($apiRequest) {
    $handler = $this->apiActions[$apiRequest['action']]['handler'];
    $leaveRequestRights = new LeaveRequestRightsService(new LeaveManagerService());

    return new $handler($apiRequest, $leaveRequestRights);
  }

  /**
   * Checks whether the request can be handled or not.
   *
   * @param $apiRequest
   *
   * @return bool
   */
  private function canHandleTheRequest($apiRequest) {
    $isTargetAction = array_key_exists($apiRequest['action'], $this->apiActions);
    return $apiRequest['entity'] === 'LeaveRequest' && $isTargetAction;
  }

  /**
   * Checks If the API request output can be handled.
   *
   * @param $apiRequest
   *
   * @return bool
   */
  private function canHandleAPiOutput($apiRequest) {
    return $this->canHandleTheRequest($apiRequest);
  }

  /**
   * Checks If the API request input can be handled.
   *
   * @param array $apiRequest
   *
   * @return bool
   */
  private function canHandleApiInput($apiRequest) {
    $canHandleRequest = $this->canHandleTheRequest($apiRequest);
    $requiresContactID = !empty($this->apiActions[$apiRequest['action']]['request_requires_contact_id']);

    return $canHandleRequest && $requiresContactID;
  }

  /**
   * Ensures that the return parameter for the API input has all the dependencies
   * needed to make the API handlers work. In this case the contact_id should be
   * present in the list of fields to be returned in the result set so that the API
   * handlers that requires this can return accurate results.
   *
   * @param array $apiRequest
   *
   */
  private function setRequestReturnParameter(&$apiRequest) {
    $options = _civicrm_api3_get_options_from_params($apiRequest['params']);
    $returnParams = array_keys($options['return']);

    if (!empty($returnParams)) {
      if (FALSE === array_search('contact_id', $returnParams)) {
        $apiRequest['params']['initial_return'] = $returnParams;
        $returnParams[] = 'contact_id';
        $apiRequest['params']['return'] = $returnParams;
      }
    }
  }
}
