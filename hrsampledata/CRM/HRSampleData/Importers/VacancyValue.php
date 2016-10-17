<?php


/**
 * Class CRM_HRSampleData_Importers_VacancyValue
 *
 */
class CRM_HRSampleData_Importers_VacancyValue extends CRM_HRSampleData_Importers_CustomFields
{

  public function __construct() {
    parent::__construct('application_case');
  }

  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('case_mapping', $row['entity_id']);

    $row['vacancy_id'] = $this->getDataMapping('vacancy_mapping', $row['vacancy_id']);;

    parent::insertRecord($row);
  }

}
