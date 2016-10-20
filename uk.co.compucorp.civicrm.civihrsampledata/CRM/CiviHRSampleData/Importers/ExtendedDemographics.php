<?php

/**
 * Class CRM_CiviHRSampleData_Importer_ExtendedDemographics
 *
 */
class CRM_CiviHRSampleData_Importer_ExtendedDemographics extends CRM_CiviHRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Extended_Demographics');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::insertRecord($row);
  }

}
