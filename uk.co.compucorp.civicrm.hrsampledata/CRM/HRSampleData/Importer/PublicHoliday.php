<?php

/**
 * Class CRM_HRSampleData_Importer_PublicHoliday
 */
class CRM_HRSampleData_Importer_PublicHoliday extends CRM_HRSampleData_CSVImporterVisitor {

  public function __construct() {
    $this->removeAllPublicHolidays();
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $this->callAPI('PublicHoliday', 'create', $row);
  }

  /**
   * Removes existing public holidays.
   */
  private function removeAllPublicHolidays() {
    $this->callAPI('PublicHoliday', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'api.PublicHoliday.delete' => ['id' => "\$value.id"],
    ]);
  }

}
