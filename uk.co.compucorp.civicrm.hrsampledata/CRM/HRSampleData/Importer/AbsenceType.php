<?php

/**
 * Class CRM_HRSampleData_Importer_AbsenceType
 */
class CRM_HRSampleData_Importer_AbsenceType extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $absenceTypeExists = $this->callAPI('HRAbsenceType', 'getcount', ['name' => $row['name']]);
    if (!$absenceTypeExists) {
      $this->callAPI('HRAbsenceType', 'create', $row);
    }
  }

}
