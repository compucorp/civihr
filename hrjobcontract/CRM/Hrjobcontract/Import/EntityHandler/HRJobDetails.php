<?php

class CRM_Hrjobcontract_Import_EntityHandler_HRJobDetails extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct() {
    parent::__construct('HRJobDetails');
  }

  /**
   * @inheritdoc
   */
  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $entityParams = $this->extractFields($params);

    if(count($entityParams) === 0) {
      return array();
    }

    $entityParams['import'] = 1;
    $entityParams['jobcontract_id'] = $contractRevision->jobcontract_id;
    $entityParams['jobcontract_revision_id'] = $contractRevision->id;

    $detailsInstance = CRM_Hrjobcontract_BAO_HRJobDetails::create($entityParams);
    if($this->isCurrent($entityParams)) {
      CRM_Hrjobcontract_BAO_HRJobContract::changePrimary($contractRevision->jobcontract_id);
    }
    return array($detailsInstance);
  }

  /**
   * Check if the contract is current
   */
  private function isCurrent($entityParams) {
    $now = new DateTime();
    $startDate = DateTime::createFromFormat('YmdHis', $entityParams['period_start_date']);
    $endDate = null;
    if(isset($entityParams['period_end_date'])) {
      $endDate = DateTime::createFromFormat('YmdHis', $entityParams['period_end_date']);
    }

    return $now >= $startDate && ($endDate == null || $now <= $endDate);
  }
}
