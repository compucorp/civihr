<?php


/**
 * Class CRM_HRSampleData_Importers_Activity
 *
 */
class CRM_HRSampleData_Importers_Activity extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');

    if (!empty($row['source_record_id'])) {
      $row['source_record_id'] = $this->getDataMapping('activity_mapping', $row['source_record_id']);
    }

    $row['source_contact_id'] = $this->getDataMapping('contact_mapping', $row['source_contact_id']);

    if (isset($row['assignee_id']) && $row['assignee_id'] != '') {
      $row['assignee_id'] = $this->getDataMapping('contact_mapping', $row['assignee_id']);
    }

    if (isset($row['target_id']) && $row['target_id'] != '') {
      $row['target_id'] = $this->getDataMapping('contact_mapping', $row['target_id']);
    }

    $result = $this->callAPI('Activity', 'create', $row);

    $this->setDataMapping('activity_mapping', $currentID, $result['id']);
  }

}
