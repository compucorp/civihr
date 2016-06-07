<?php

class CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct() {
    parent::__construct('HRJobLeave');
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    if(!isset($params['HRJobLeave-leave_amount'])) {
      return null;
    }

    $entities = array();

    foreach ($params['HRJobLeave-leave_amount'] as $leaveTypeId => $leaveAmount) {
      $entityParams = $this->extractFields($params);
      $entityParams['import'] = 1;
      $entityParams['jobcontract_id'] = $contractRevision->jobcontract_id;
      $entityParams['jobcontract_revision_id'] = $contractRevision->id;
      $entityParams['leave_type'] = $leaveTypeId;
      $entityParams['leave_amount'] = $leaveAmount;

      $entities[] = CRM_Hrjobcontract_BAO_HRJobLeave::create($entityParams);
    }

    return $entities;
  }
}
