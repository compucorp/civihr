<?php

/**
 * Class CRM_CiviHRSampleData_Importer_AbsencePeriod
 *
 */
class CRM_CiviHRSampleData_Importer_AbsencePeriod extends CRM_CiviHRSampleData_DataImporter
{

  public function __construct() {
    $this->removeAllPeriods();
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
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
