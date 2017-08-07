<?php

/**
 * Class CRM_HRSampleData_Importer_JobRoles
 */
class CRM_HRSampleData_Importer_JobRoles extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * Stores cost centers names/values
   *
   * @var array
   */
  private $costCenters = [];


  public function __construct() {
    $this->costCenters = $this->getFixData('OptionValue', 'name', 'id', [
      'option_group_id' => 'cost_centres',
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `job_contract_id`
   */
  protected function importRecord(array $row) {
    $row['job_contract_id'] = $this->getDataMapping('contracts_mapping', $row['job_contract_id']);

    if (!empty($row['funder'])) {
      $row['funder'] = $this->getDataMapping('contact_mapping', $row['funder']);
    }

    if (!empty($row['cost_center'])) {
      $row['cost_center'] = $this->costCenters[$row['cost_center']];
    }

    $this->callAPI('HrJobRoles', 'create', $row);

  }

}
