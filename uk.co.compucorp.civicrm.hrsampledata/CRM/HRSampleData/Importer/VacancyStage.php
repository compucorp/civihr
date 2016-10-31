<?php

/**
 * Class CRM_HRSampleData_Importer_VacancyStage
 *
 */
class CRM_HRSampleData_Importer_VacancyStage extends CRM_HRSampleData_CSVHandler
{

  /**
   * Stores case statuses names/values
   *
   * @var array
   */
  private $caseStatuses = [];

  public function __construct() {
    $this->caseStatuses = $this->getFixData('OptionValue', 'name', 'value', [
      'option_group_id' => 'case_status',
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `case_status_id` & `vacancy_id`
   */
  protected function operate(array $row) {
    $row['vacancy_id'] = $this->getDataMapping('vacancy_mapping', $row['vacancy_id']);

    $row['case_status_id'] = $this->caseStatuses[$row['case_status_id']];

    $this->callAPI('HRVacancyStage', 'create', $row);
  }

}
