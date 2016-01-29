<?php

class CRM_Hrjobcontract_Import_EntityHandler_Generic extends CRM_Hrjobcontract_Import_EntityHandler {
  public function __construct($entityName) {
    parent::__construct($entityName);
  }

  public function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision) {
    $entityParams = $this->extractFields($params);

    if(count($entityParams) === 0) {
      return array();
    }

    $entityParams['import'] = 1;
    $entityParams['jobcontract_id'] = $contractRevision->jobcontract_id;
    $entityParams['jobcontract_revision_id'] = $contractRevision->id;

    $entityClass = 'CRM_Hrjobcontract_BAO_' . $this->getEntityName();
    return array(call_user_func(array($entityClass, 'create'), $entityParams));
  }
}
