<?php

/**
 * Class CRM_CiviHRSampleData_Importer_Vacancy
 *
 */
class CRM_CiviHRSampleData_Importer_Vacancy extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {

    $currentID = $this->unsetArrayElement($row, 'id');

    $result = $this->callAPI('HRVacancy', 'create', $row);

    $this->setDataMapping('vacancy_mapping', $currentID, $result['id']);
  }

}
