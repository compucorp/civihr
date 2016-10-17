<?php


/**
 * Class CRM_HRSampleData_Importers_ExtendedDemographics
 *
 */
class CRM_HRSampleData_Importers_ExtendedDemographics extends CRM_HRSampleData_Importers_CustomFields
{

  public function __construct() {
    parent::__construct('Extended_Demographics');
  }

  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::insertRecord($row);
  }

}
