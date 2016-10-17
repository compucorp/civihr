<?php


/**
 * Class CRM_HRSampleData_Importers_VacancyStage
 *
 */
class CRM_HRSampleData_Importers_VacancyStage extends CRM_HRSampleData_DataImporter
{

  /**
   * @var array To store case statuses names/values
   */
  private $caseStatuses =[];

  public function __construct() {
    $this->caseStatuses = $this->getFixData('OptionValue', 'name', 'value', [
      'option_group_id' => 'case_status',
    ]);
  }

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `case_status_id` & `vacancy_id`
   */
  protected function insertRecord(array $row) {
    $row['vacancy_id'] = $this->getDataMapping('vacancy_mapping', $row['vacancy_id']);

    $row['case_status_id'] = $this->caseStatuses[$row['case_status_id']];

    $this->callAPI('HRVacancyStage', 'create', $row);
  }

}
