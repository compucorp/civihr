<?php


/**
 * Class CRM_HRSampleData_Importers_AbsencePeriod
 *
 */
class CRM_HRSampleData_Importers_AbsencePeriod extends CRM_HRSampleData_DataImporter
{

  public function __construct() {
    $this->removeAllPeriods();
  }

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $this->callAPI('HRAbsencePeriod', 'create', $row);
  }

  /**
   * Remove any existing absence period.
   */
  private function removeAllPeriods() {
    $query = "DELETE FROM civicrm_hrabsence_period";
    CRM_Core_DAO::executeQuery($query);
  }

}
