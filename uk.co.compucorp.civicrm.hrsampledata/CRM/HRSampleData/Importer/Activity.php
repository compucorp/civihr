<?php

/**
 * Class CRM_HRSampleData_Importer_Activity
 */
class CRM_HRSampleData_Importer_Activity extends CRM_HRSampleData_CSVImporterVisitor {

  /**
   * @var array
   *  Caches the Task Activity Types loaded by the API
   */
  private $taskActivityTypes;

  /**
   * @var array
   *  Caches the Document Activity Types loaded by the API
   */
  private $documentActivityTypes;

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');

    if (!empty($row['source_record_id'])) {
      $row['source_record_id'] = $this->getDataMapping('activity_mapping', $row['source_record_id']);
    }

    $row['source_contact_id'] = $this->getDataMapping('contact_mapping', $row['source_contact_id']);

    if (!empty($row['assignee_id'])) {
      $row['assignee_id'] = $this->getDataMapping('contact_mapping', $row['assignee_id']);
    }

    if (!empty($row['target_id'])) {
      $row['target_id'] = $this->getDataMapping('contact_mapping', $row['target_id']);
    }

    $isTask = (bool)$this->unsetArrayElement($row, 'is_task');
    $isDocument = (bool)$this->unsetArrayElement($row, 'is_document');

    if($isTask) {
      $row['activity_type_id'] = $this->getTaskActivityTypeId($row['activity_type_id']);
    }

    if($isDocument) {
      $row['activity_type_id'] = $this->getDocumentActivityTypeId($row['activity_type_id']);
    }

    $result = $this->callAPI('Activity', 'create', $row);

    $this->setDataMapping('activity_mapping', $currentID, $result['id']);
  }

  /**
   * Returns the ID for the given Activity Type for the CiviTasks component
   *
   * @param string $activityTypeLabel
   *
   * @return mixed
   */
  private function getTaskActivityTypeId($activityTypeLabel) {
    if(!$this->taskActivityTypes) {
      $this->taskActivityTypes = $this->getActivityTypesByComponent('CiviTask');
    }

    return CRM_Utils_Array::value($activityTypeLabel, $this->taskActivityTypes, $activityTypeLabel);
  }

  /**
   * Returns the ID for the given Activity Type for the CiviDocuments component
   *
   * @param string $activityTypeLabel
   *
   * @return mixed
   */
  private function getDocumentActivityTypeId($activityTypeLabel) {
    if(!$this->documentActivityTypes) {
      $this->documentActivityTypes = $this->getActivityTypesByComponent('CiviDocument');
    }

    return CRM_Utils_Array::value($activityTypeLabel, $this->documentActivityTypes, $activityTypeLabel);
  }

  /**
   * Loads a list of Activity Types for the given Component
   *
   * The list format is: [activity type label => activity type id]
   *
   * @param string $component
   *
   * @return array
   */
  private function getActivityTypesByComponent($component) {
    $componentID = CRM_Core_Component::getComponentIDs()[$component];

    $result = civicrm_api3('OptionGroup', 'get', [
      'sequential' => 1,
      'name' => 'activity_type',
      'api.OptionValue.get' => [
        'option_group_id' => '$value.id',
        'component_id' => $componentID
      ],
    ]);

    if(empty($result['values'][0]['api.OptionValue.get']['values'])) {
      return [];
    }

    $activityTypes = [];

    foreach($result['values'][0]['api.OptionValue.get']['values'] as $activityType) {
      $activityTypes[$activityType['label']] = $activityType['value'];
    }

    return $activityTypes;
  }

}
