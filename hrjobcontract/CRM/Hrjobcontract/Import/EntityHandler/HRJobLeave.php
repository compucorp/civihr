<?php

use CRM_Hrjobcontract_DAO_HRJobContractRevision as HRJobContractRevision;
use CRM_Hrjobcontract_ExportImportValuesConverter as ImportExportUtility;

class CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave extends CRM_Hrjobcontract_Import_EntityHandler {

  public function __construct() {
    parent::__construct('HRJobLeave');
  }

  public function handle(array $params, HRJobContractRevision $contractRevision, array &$previousRevision) {
    $leaveData = $this->prepareLeaveData(
      $params, $contractRevision->jobcontract_id, $contractRevision->id
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
   * @param array $params
   * @param int $contractID
   * @param int $revisionID
   *
   * @return array
   */
  private function prepareLeaveData($params, $contractID, $revisionID) {
    $leaveRows = [];
    $importExportUtility = ImportExportUtility::singleton();
    $leaveTypes = $importExportUtility->getLeaveTypes();

    foreach ($leaveTypes as $leaveType) {
      $key = filter_var($leaveType['title'], FILTER_SANITIZE_STRING);
      $leaveAmount = !empty($params[$key]) ? $params[$key] : 0;
      $leaveRows[] = [
        'leave_type' => $leaveType['id'],
        'leave_amount' => $leaveAmount,
        'add_public_holidays' => $leaveType['add_public_holiday_to_entitlement'],
        'jobcontract_revision_id' => $revisionID,
        'jobcontract_id' => $contractID,
      ];

      return $leaveRows;
    }
  }
}


