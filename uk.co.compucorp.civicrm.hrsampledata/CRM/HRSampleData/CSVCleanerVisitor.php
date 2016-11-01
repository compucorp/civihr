<?php

use CRM_HRSampleData_CSVProcessingVisitor as CSVProcessingVisitor;

abstract class CRM_HRSampleData_CSVCleanerVisitor extends CSVProcessingVisitor {

  /**
   * Deletes entity record from the database based on
   * search criteria
   *
   * @param string $entity
   * @param array $searchParams
   */
  protected function deleteRecord($entity, $searchParams) {
    $searchParams['options'] = ['limit' => 0];

    $entityResult = $this->callAPI($entity, 'get', $searchParams);

    if (!empty($entityResult['id'])) {
      $entityID = $entityResult['id'];
      $this->callAPI($entity, 'delete', ['id' => $entityID]);
    }
  }

}
