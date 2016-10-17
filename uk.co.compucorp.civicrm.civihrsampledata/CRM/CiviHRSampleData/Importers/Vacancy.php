<?php


/**
 * Class CRM_CiviHRSampleData_Importers_Vacancy
 *
 */
class CRM_CiviHRSampleData_Importers_Vacancy extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {

    $currentID = $this->unsetArrayElement($row, 'id');

    $result = $this->callAPI('HRVacancy', 'create', $row);

    $this->setDataMapping('vacancy_mapping', $currentID, $result['id']);
  }

}
