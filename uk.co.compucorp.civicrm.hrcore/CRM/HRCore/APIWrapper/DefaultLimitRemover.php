<?php

class CRM_HRCore_APIWrapper_DefaultLimitRemover implements API_Wrapper {

  /**
   * In civicrm API, 0 means there is no limit on the retrieved results
   */
  const NO_LIMIT_ON_RESULTS = 0;

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
      $apiRequest['params']['options']['limit'] = self::NO_LIMIT_ON_RESULTS;
    }
  }
}
