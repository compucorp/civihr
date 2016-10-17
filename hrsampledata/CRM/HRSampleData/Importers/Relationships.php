<?php


/**
 * Class CRM_HRSampleData_Importers_Relationships
 *
 */
class CRM_HRSampleData_Importers_Relationships extends CRM_HRSampleData_DataImporter
{

  /**
   * @var array To store relationships types IDs/Names
   */
  private $relationshipTypes =[];

  public function __construct() {
    $this->relationshipTypes =$this->getFixData('RelationshipType', 'name_a_b', 'id');
  }

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `contact_id_a`, `contact_id_b` & `relationship_type_id`
   */
  protected function insertRecord(array $row) {
    $row['contact_id_a'] = $this->getDataMapping('contact_mapping', $row['contact_id_a']);
    $row['contact_id_b'] = $this->getDataMapping('contact_mapping', $row['contact_id_b']);

    $row['relationship_type_id'] = $this->relationshipTypes[$row['relationship_type_id']];

    if (isset($row['case_id'])) {
      if (!empty($row['case_id'])) {
        $row['case_id'] = $this->getDataMapping('case_mapping', $row['case_id']);
      }
      else{
        unset($row['case_id']);
      }
    }

    $this->callAPI('Relationship', 'create', $row);
  }

}
