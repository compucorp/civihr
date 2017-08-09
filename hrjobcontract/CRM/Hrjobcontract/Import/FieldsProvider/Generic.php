<?php

class CRM_Hrjobcontract_Import_FieldsProvider_Generic extends CRM_Hrjobcontract_Import_FieldsProvider {
  /**
   * @param string $entityName The name of entity (e.g. HRJobPay)
   */
  public function __construct($entityName) {
    parent::__construct($entityName);
  }

  /**
   * Get the available fields
   *
   * @return array
   */
  public function provide() {
    $entityName = 'CRM_Hrjobcontract_BAO_' . $this->getEntityName();

    if ($this->getEntityName() == 'HRJobLeave') {
      return [];
    }

    $importableFields = call_user_func(array(
      $entityName,
      'importableFields'
    ), $this->getEntityName(), NULL);


    return array_merge(
      $this->createDivider($this->getDividerTitle()),
      $this->convertImportableFields($importableFields)
    );
  }

  /**
   * Get the title of "divider" (i.e. header in select element)
   * @return string
   */
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
