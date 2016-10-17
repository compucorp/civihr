<?php


/**
 * Class CRM_HRSampleData_Importers_Vacancy
 *
 */
class CRM_HRSampleData_Importers_Vacancy extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {

    $currentID = $this->unsetArrayElement($row, 'id');

    $result = $this->callAPI('HRVacancy', 'create', $row);

    $this->setDataMapping('vacancy_mapping', $currentID, $result['id']);
  }

}
