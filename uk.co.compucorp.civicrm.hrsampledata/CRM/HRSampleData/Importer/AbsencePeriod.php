<?php

/**
 * Class CRM_HRSampleData_Importer_AbsencePeriod
 */
class CRM_HRSampleData_Importer_AbsencePeriod extends CRM_HRSampleData_CSVImporterVisitor {

  public function __construct() {
    $this->removeAllPeriods();
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');
    $absencePeriod = $this->callAPI('AbsencePeriod', 'create', $row);
    $this->setDataMapping('absence_period_mapping', $currentID, $absencePeriod['id']);
  }

  /**
   * Removes existing absence period.
   */
  private function removeAllPeriods() {
    $this->callAPI('AbsencePeriod', 'get', [
      'return' => ['id'],
      'api.AbsencePeriod.delete' => ['id' => '$value.id'],
    ]);
  }

}
