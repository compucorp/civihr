<?php

require_once 'api/Wrapper.php';

class CRM_HRCore_APIWrapper_DefaultLimitRemover implements API_Wrapper {

  /**
   * In civicrm API, 0 means there is no limit on the retrieved results
   */
  static private $DEFAULT_NO_LIMIT_VALUE = 0;

  public function getDefaultNoLimitValue() {
    return self::$DEFAULT_NO_LIMIT_VALUE;
  }

  /**
   * {@inheritDoc}
   */
  public function fromApiInput($apiRequest) {
    $this->removeDefaultLimit($apiRequest);

    return $apiRequest;
  }

  /**
   * {@inheritDoc}
   */
  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

  /**
   * Removes the default API limit if it's not set
   *
   * @param $apiRequest
   */
  private function removeDefaultLimit(&$apiRequest) {
    if (empty($apiRequest['params']['options']['limit'])) {
      $apiRequest['params']['options']['limit'] = $this->getDefaultNoLimitValue();
    }
  }
}
