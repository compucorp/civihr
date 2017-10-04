<?php

use CRM_HRSampleData_CSVProcessingVisitor as CSVProcessingVisitor;

abstract class CRM_HRSampleData_CSVCleanerVisitor extends CSVProcessingVisitor {

  /**
   * Deletes entity record from the database based on
   * search criteria if the record delete_on_uninstall field
   * is set.
   *
   * @param string $entity
   * @param array $searchParams
   * @param boolean $deleteOnUninstall
   */
  protected function deleteRecord($entity, $searchParams, $deleteOnUninstall = TRUE) {
    if (!$deleteOnUninstall) {
      return;
    }

    $searchParams['options'] = ['limit' => 0];
    $entityResult = $this->callAPI($entity, 'get', $searchParams);
    if (!empty($entityResult['id'])) {
      $entityID = $entityResult['id'];
      $this->callAPI($entity, 'delete', ['id' => $entityID]);
    }
  }
}
