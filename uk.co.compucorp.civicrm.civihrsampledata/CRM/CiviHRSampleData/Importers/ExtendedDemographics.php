<?php


/**
 * Class CRM_CiviHRSampleData_Importers_ExtendedDemographics
 *
 */
class CRM_CiviHRSampleData_Importers_ExtendedDemographics extends CRM_CiviHRSampleData_Importers_CustomFields
{

  public function __construct() {
    parent::__construct('Extended_Demographics');
  }

  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::insertRecord($row);
  }

}
