<?php

/**
 * Class CRM_HRSampleData_Importer_Vacancy
 */
class CRM_HRSampleData_Importer_Vacancy extends CRM_HRSampleData_CSVImporterVisitor
{

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

    $currentID = $this->unsetArrayElement($row, 'id');

    $result = $this->callAPI('HRVacancy', 'create', $row);

    $this->setDataMapping('vacancy_mapping', $currentID, $result['id']);
  }

}
