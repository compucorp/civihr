<?php

class CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct() {
    parent::__construct('HRJobLeave');
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $leaveAmounts = [];
    if (!empty($params['HRJobLeave-leave_amount'])) {
      $leaveAmounts = $params['HRJobLeave-leave_amount'];
    }

    $leaveData = $this->prepareLeaveData(
      $leaveAmounts, $contractRevision->jobcontract_id, $contractRevision->id
    );

    return civicrm_api3('HRJobLeave', 'replace', [
      'sequential' => 1,
      'values' => $leaveData,
      'jobcontract_id' => $contractRevision->jobcontract_id,
      'jobcontract_revision_id' => $contractRevision->id,
    ])['values'][0];
  }

  /**
   * Prepares Job Leave entity data to a valid API format.
   *
   * @param array $leaveEntitlements
   *   Job leave entity data.
   * @param int $contractID
   * @param int $revisionID
   *
   * @return array
   */
  private function prepareLeaveData($leaveEntitlements, $contractID, $revisionID) {
    $leaveRows = [];
    $leaveTypes = CRM_Hrjobcontract_SelectValues::buildLeaveTypes();

    foreach($leaveTypes as $leaveType) {
      $leaveAmount = isset($leaveEntitlements[$leaveType['id']]) ?  $leaveEntitlements[$leaveType['id']] : 0;

      $leaveRows[] = [
        'leave_type' => "{$leaveType['id']}",
        'leave_amount' => "{$leaveAmount}",
        'add_public_holidays' => "0",
        "jobcontract_revision_id" => "{$revisionID}",
        "jobcontract_id" => "{$contractID}",
      ];
    }

    return $leaveRows;
  }
}
