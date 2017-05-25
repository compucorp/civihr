<?php

/**
 * This class is basically a wrapper around the HRJobContract API.
 *
 * It has some utility methods which make the job of calling that API easier by
 * automatically adding some default params and parsing the returned result.
 */
class CRM_HRLeaveAndAbsences_Service_JobContract {

  /**
   * Uses the HRJobContract.get API endpoint to fetch the contract with the
   * given ID.
   *
   * It returns an array with all the job contract fields + the contract details
   *
   * @param int $id
   *   The ID of the Contract
   *
   * @return array|null
   *   The output of the API or null if no contract was found
   */
  public function getContractByID($id) {
    $returnFields = [
      'id',
      'contact_id',
      'position',
      'title',
      'funding_notes',
      'contract_type',
      'period_start_date',
      'period_end_date',
      'end_reason',
      'notice_amount',
      'notice_amount_employee',
      'notice_unit_employee',
      'location'
    ];

    $result = $this->callHRJobContractGet([
      'sequential' => 1,
      'id'         => $id,
      'return'     => $returnFields
    ]);

    if(!$result) {
      return null;
    }

    $contract = $result[0];

    if(!array_key_exists('period_end_date', $contract)) {
      $contract['period_end_date'] = null;
    }

    return $contract;
  }

  /**
   * Uses the HRJobContract.getcontractswithdetailsinperiod API endpoint to
   * return a list of contracts for the given overlapping the given start and
   * end dates
   *
   * @param \DateTime $startDate
   * @param \DateTime|NULL $endDate
   * @param array $contactID
   *
   * @return mixed
   */
  public function getContractsForPeriod(DateTime $startDate, DateTime $endDate, array $contactID = []) {
    $result = $this->callHRJobContractAPI('getcontractswithdetailsinperiod', [
      'start_date' => $startDate->format('Y-m-d'),
      'end_date' => $endDate->format('Y-m-d'),
      'contact_id' => !empty($contactID) ? ['IN' => $contactID] : []
    ]);

    return $result['values'];
  }

  /**
   * Wrapper for the get action on the HRJobContract API
   *
   * @param array $params
   *   An array of $params to be passed to the API
   *
   * @return array|null
   */
  private function callHRJobContractGet($params) {
    $result = $this->callHRJobContractAPI('get', $params);

    if(empty($result['values'])) {
      return null;
    }

    return $result['values'];
  }

  /**
   * Wrapper for the HRJobContract API
   *
   * @param string $action
   *   The API action to be executed (e.g. get, delete, etc)
   * @param array $params
   *   An array of $params to be passed to the API
   *
   * @return array
   */
  private function callHRJobContractAPI($action, $params) {
    $defaultParams = ['sequential' => 1];
    $params = array_merge($defaultParams, $params);

    return civicrm_api3('HRJobContract', $action, $params);
  }

}
