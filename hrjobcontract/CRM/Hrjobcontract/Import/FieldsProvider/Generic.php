<?php

class CRM_Hrjobcontract_Import_FieldsProvider_Generic extends CRM_Hrjobcontract_Import_FieldsProvider {
  public function __construct($entityName) {
    parent::__construct($entityName);
  }

  public function provide() {
    $entityName = 'CRM_Hrjobcontract_BAO_' . $this->getEntityName();

    $importableFields = call_user_func(array(
      $entityName,
      'importableFields'
    ), $this->getEntityName(), NULL);


    return array_merge(
      $this->createDivider($this->getDividerTitle()),
      $this->convertImportableFields($importableFields)
    );
  }

  private function getDividerTitle() {
    $entityName = $this->getEntityName();

    $displayNames = array(
      'HRJobLeave' => 'leave',
      'HRJobContractRevision' => 'contract revision',
      'HRJobContract' => 'contract',
      'HRJobDetails' => 'details',
      'HRJobHealth' => 'health',
      'HRJobPension' => 'pension',
      'HRJobHour' => 'hours',
      'HRJobPay' => 'pay'
    );
    return sprintf('- %s fields -', isset($displayNames[$entityName]) ? $displayNames[$entityName] : $entityName);
  }
}
