<?php

class CRM_Hrjobcontract_Import_EntityHandler_Generic extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct($entityName) {
    parent::__construct($entityName);
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $entityParams = $this->extractFields($params);

    $entityParams['sequential'] = 1;
    $entityParams['jobcontract_id'] = $contractRevision->jobcontract_id;
    $entityParams['jobcontract_revision_id'] = $contractRevision->id;

    return civicrm_api3($this->getEntityName(), 'create', $entityParams)['values'][0];
  }
}
