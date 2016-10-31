<?php

/**
 * Class CRM_HRSampleData_Importer_AbsenceType
 *
 */
class CRM_HRSampleData_Importer_AbsenceType extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    $absenceTypeExists = $this->callAPI('HRAbsenceType', 'getcount', ['name' => $row['name']]);
    if (!$absenceTypeExists) {
      $this->callAPI('HRAbsenceType', 'create', $row);
    }
  }

}
