<?php

/**
 * Class CRM_CiviHRSampleData_Importer_JobRoles
 *
 */
class CRM_CiviHRSampleData_Importer_JobRoles extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * Stores cost centers names/values
   *
   * @var array
   */
  private $costCenters = [];


  public function __construct() {
    $this->costCenters = $this->getFixData('OptionValue', 'name', 'value', [
      'option_group_id' => 'cost_centres',
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `job_contract_id`
   */
  protected function insertRecord(array $row) {
    $row['job_contract_id'] = $this->getDataMapping('contracts_mapping', $row['job_contract_id']);

    if (!empty($row['funder'])) {
      $row['funder'] = $this->getDataMapping('contact_mapping', $row['funder']);
    }

    if (!empty($row['cost_center'])) {
      $row['cost_center'] = $this->costCenters[$row['cost_center']];
    }

    $this->callAPI('HrJobRoles', 'get', $row);

  }

}
