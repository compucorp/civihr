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
    $this->callAPI('AbsencePeriod', 'create', $row);
  }

  /**
   * Removes existing absence period.
   */
  private function removeAllPeriods() {
    $this->callAPI('AbsencePeriod', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'api.AbsencePeriod.delete' => ['id' => "\$value.id"],
    ]);
  }

}
