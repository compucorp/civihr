<?php

use CRM_Hrjobcontract_ExportImportValuesConverter as ImportExportUtility;

class CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave extends CRM_Hrjobcontract_Import_EntityHandler {

  public function __construct() {
    parent::__construct('HRJobLeave');
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $importExportUtility = ImportExportUtility::singleton();
    $leaveTypes = $importExportUtility->getLeaveTypes();
    $leaveData = [];

    foreach($leaveTypes as $leaveType) {
      $key = filter_var($leaveType['title'], FILTER_SANITIZE_STRING);
      if(!empty($params[$key])){
        $leaveData[] = [
          'leave_type' => $leaveType['id'],
          'leave_amount' => "{$params[$leaveType['title']]}",
          'add_public_holidays' => $leaveType['add_public_holiday_to_entitlement'],
          "jobcontract_revision_id" => $contractRevision->id,
          "jobcontract_id" => $contractRevision->jobcontract_id,
        ];
      }
    }

    return civicrm_api3('HRJobLeave', 'replace', [
      'sequential' => 1,
      'values' => $leaveData,
      'jobcontract_id' => $contractRevision->jobcontract_id,
      'jobcontract_revision_id' => $contractRevision->id,
    ])['values'][0];
  }
}
