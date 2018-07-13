<?php

class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveCalendarFeedFilterFields implements API_Wrapper {

  /**
   * {@inheritdoc}
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $this->unserializeFilterValues($result['values']);
    }

    return $result;
  }

  /**
   * Checks whether the request can be handled or not.
   *
   * @param array $apiRequest
   *
   * @return bool
   */
  private function canHandleTheRequest($apiRequest) {
    $targetActions = ['get'];
    $isTargetAction = in_array($apiRequest['action'], $targetActions);

    return $apiRequest['entity'] === 'LeaveRequestCalendarFeedConfig' && $isTargetAction;
  }

  /**
   * The filter fields (composed_of, visible_to) are stored in the database
   * as serialized values. This function basically unserializes the values
   * for these fields.
   *
   * @param array $result
   */
  private function unserializeFilterValues(&$result) {
    $filterFields = ['composed_of', 'visible_to'];
    foreach ($result as $key => $value) {
      foreach ($filterFields as $field) {
        if (isset($value[$field])) {
          $result[$key][$field] = unserialize($value[$field]);
        }
      }
    }
  }
}
