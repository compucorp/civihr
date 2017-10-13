<?php

trait CRM_HRLeaveAndAbsences_ContractHelpersTrait {

  protected $contract;

  protected function createContract() {
    $result = civicrm_api3('HRJobContract', 'create', [
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1,
      'sequential' => 1
    ]);
    $this->contract = $result['values'][0];
  }

  protected function setContractDates($startDate, $endDate) {
    civicrm_api3('HRJobDetails', 'create', [
      'jobcontract_id' => $this->contract['id'],
      'period_start_date' => $startDate,
      'period_end_date' => $endDate,
    ]);
  }
}
