<?php

/**
 * Class CRM_HRSampleData_Importer_AbsencePeriod
 */
class CRM_HRSampleData_Importer_AbsencePeriod extends CRM_HRSampleData_CSVImporterVisitor
{

  public function __construct() {
    $this->removeAllPeriods();
  }

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->importRecord($row);
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $this->callAPI('HRAbsencePeriod', 'create', $row);
  }

  /**
   * Removes existing absence period.
   */
  private function removeAllPeriods() {
    $absencePeriods = $this->callAPI('HRAbsencePeriod', 'get', [
      'sequential' => 1,
      'return' => ["id"],
      'options' => ['limit' => 0],
    ]);

    foreach($absencePeriods['values'] as $absencePeriod) {
      $this->callAPI('HRAbsencePeriod', 'delete', [
        'id' => $absencePeriod['id'],
      ]);
    }
  }

}
