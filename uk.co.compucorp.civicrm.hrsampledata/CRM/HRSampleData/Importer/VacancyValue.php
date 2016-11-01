<?php

/**
 * Class CRM_HRSampleData_Importer_VacancyValue
 */
class CRM_HRSampleData_Importer_VacancyValue extends CRM_HRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('application_case');
  }

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
    $row['entity_id'] = $this->getDataMapping('case_mapping', $row['entity_id']);

    $row['vacancy_id'] = $this->getDataMapping('vacancy_mapping', $row['vacancy_id']);;

    parent::importRecord($row);
  }

}
