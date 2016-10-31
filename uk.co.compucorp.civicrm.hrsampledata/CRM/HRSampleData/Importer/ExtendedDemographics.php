<?php

/**
 * Class CRM_HRSampleData_Importer_ExtendedDemographics
 *
 */
class CRM_HRSampleData_Importer_ExtendedDemographics extends CRM_HRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Extended_Demographics');
  }

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::operate($row);
  }

}
