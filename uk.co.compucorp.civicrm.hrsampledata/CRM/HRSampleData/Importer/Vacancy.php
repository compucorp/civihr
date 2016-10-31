<?php

/**
 * Class CRM_HRSampleData_Importer_Vacancy
 */
class CRM_HRSampleData_Importer_Vacancy extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {

    $currentID = $this->unsetArrayElement($row, 'id');

    $result = $this->callAPI('HRVacancy', 'create', $row);

    $this->setDataMapping('vacancy_mapping', $currentID, $result['id']);
  }

}
