<?php

class CRM_Hrjobcontract_Import_EntityHandler_HRJobRole extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct() {
    parent::__construct('HRJobRole');
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $entityParams = $this->extractFields($params);

    if(count($entityParams) === 0) {
      return null;
    }

    $entityParams['import'] = 1;
    $entityParams['job_contract_id'] = $contractRevision->jobcontract_id;

    return array(CRM_HRjobroles_BAO_HrJobRoles::create($entityParams));
  }
}
