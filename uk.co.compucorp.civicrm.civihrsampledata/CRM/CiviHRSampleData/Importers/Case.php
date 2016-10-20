<?php

/**
 * Class CRM_CiviHRSampleData_Importer_Case
 *
 */
class CRM_CiviHRSampleData_Importer_Case extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `id` & `contact_type`
   */
  protected function insertRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');

    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);

    $result = $this->callAPI('Case', 'create', $row);

    $this->setDataMapping('case_mapping', $currentID, $result['id']);
  }

}
