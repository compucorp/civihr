<?php

/**
 * Class CRM_HRSampleData_Importer_Relationships
 *
 */
class CRM_HRSampleData_Importer_Relationships extends CRM_HRSampleData_DataImporter
{

  /**
   * Stores relationships types IDs/Names
   *
   * @var array
   */
  private $relationshipTypes = [];

  public function __construct() {
    $this->relationshipTypes =$this->getFixData('RelationshipType', 'name_a_b', 'id');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id_a`, `contact_id_b` & `relationship_type_id`
   */
  protected function insertRecord(array $row) {
    $row['contact_id_a'] = $this->getDataMapping('contact_mapping', $row['contact_id_a']);
    $row['contact_id_b'] = $this->getDataMapping('contact_mapping', $row['contact_id_b']);

    $row['relationship_type_id'] = $this->relationshipTypes[$row['relationship_type_id']];

    $this->callAPI('Relationship', 'create', $row);
  }

}
