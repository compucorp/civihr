<?php

class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestDates implements API_Wrapper {

  /**
   * Adds Hours to the from_date and to_date fields, in case they're present but
   * don't have that information.
   *
   * For the from_date field, the hour will be set to 00:00:00. For the to_date
   * field, it will be set to 23:59:59
   *
   * @param array $apiRequest
   *
   * @return array
   *   modified $apiRequest
   */
  public function fromApiInput($apiRequest) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $this->prepareDates($apiRequest);
    }

    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

  /**
   * Returns whether this Wrapper can handle the given $apiRequest or not. It
   * can only handle the `get` and `getFull` actions of the LeaveRequest API.
   *
   * @param array $apiRequest
   *
   * @return bool
   */
  private function canHandleTheRequest($apiRequest) {
    $targetActions = ['get', 'getfull'];
    $isTargetAction = in_array($apiRequest['action'], $targetActions);

    return $apiRequest['entity'] === 'LeaveRequest' && $isTargetAction;
  }

  /**
   * If the query contains values for the from_date and to_date fields, this
   * method makes sure they will always contain information about the hour.
   *
   * For the from_date field, it will set the hour as 00:00:00 if it's not
   * present. For the to_date field, it will set it as 23:59:59 if it's not
   * present.
   *
   * These are datetime fields, and the reason for this is that if there's a
   * query like "to_date <= 2018-01-09" it will include all the leave requests
   * ending on that date, regardless of the hour. Without this, such a query
   * would be interpreted as "to_date <= 2018-01-09 00:00:00" by the database,
   * and most of the requests ending on 2018-01-09 would not be included in the
   * result.
   *
   * @param array $apiRequest
   */
  private function prepareDates(&$apiRequest) {
    $params = &$apiRequest['params'];

    $fromDate = $this->getValueFromParams($params, 'from_date');

    if ($this->dateIsValid($fromDate) && $this->dateIsMissingHours($fromDate)) {
      $fromDate = (new DateTime($fromDate))->format('Y-m-d 00:00:00');
      $this->setValueOfParam($params, 'from_date', $fromDate);
    }

    $toDate = $this->getValueFromParams($params, 'to_date');

    if ($this->dateIsValid($toDate) && $this->dateIsMissingHours($toDate)) {
      $toDate = (new DateTime($toDate))->format('Y-m-d 23:59:59');
      $this->setValueOfParam($params, 'to_date', $toDate);
    }
  }

  /**
   * Check whether the given value is a valid date.
   *
   * @param string $date
   *
   * @return bool
   */
  private function dateIsValid($date) {
    $date = date_parse($date);

    return $date['error_count'] === 0 && $date['warning_count'] === 0;
  }

  /**
   * Checks if the given $date string contains information about hours
   *
   * @param string $date
   *  A string in any of the formats supported by strtotime()
   *
   * @return bool
   */
  private function dateIsMissingHours($date) {
    $date = date_parse($date);

    return $date['hour'] === FALSE ||
           $date['minute'] === FALSE ||
           $date['second'] === FALSE;
  }

  /**
   * Gets the value of the given $field in the $params array.
   *
   * This method works for both simple params ($field => $value) and params with
   * operators ($field => [$operator => $value])
   *
   * @param array $params
   * @param string $field
   *
   * @return mixed|null
   */
  private function getValueFromParams($params, $field) {
    if (empty($params[$field])) {
      return NULL;
    }

    // When using other operators than =
    // the param will be an array in this format:
    // [<operator> => <value>]
    if (is_array($params[$field])) {
      return reset($params[$field]);
    }

    return $params[$field];
  }

  /**
   * Sets the value of the given $field in $params to the given $value.
   *
   * This method works for both simple params ($field => $value) and params with
   * operators ($field => [$operator => $value]).
   *
   * @param array $params
   * @param string $field
   * @param mixed $value
   */
  private function setValueOfParam(&$params, $field, $value) {
    if (!array_key_exists($field, $params)) {
      return;
    }

    if (is_array($params[$field])) {
      $key = key($params[$field]);
      $params[$field][$key] = $value;
    }
    else {
      $params[$field] = $value;
    }
  }

}
