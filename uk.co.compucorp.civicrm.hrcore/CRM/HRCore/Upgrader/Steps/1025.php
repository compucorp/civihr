<?php

trait CRM_HRCore_Upgrader_Steps_1025 {

  /**
   * Deletes some demographics fields where they are not in use
   *
   * @return bool
   */
  public function upgrade_1025() {

    $this->deleteExtendDemographicFields([
      'Ethnicity',
      'Religion',
      'Sexual_Orientation',
    ]);

    return TRUE;
  }

  /**
   * Deletes Custom Demographic Fields only if they are not used
   *
   * @param $fieldsToDelete
   */
  private function deleteExtendDemographicFields($fieldsToDelete) {
    $customGroup = civicrm_api3('CustomGroup', 'get', [
      'name' => ['LIKE' => 'Extended_Demographics'],
    ]);
    $customGroup = array_shift($customGroup['values']);
    $customFields = civicrm_api3('CustomField', 'get', [
      'name' => ['IN' => $fieldsToDelete],
    ]);
    foreach ($customFields['values'] as $customField) {
      $query = 'SELECT * FROM ' . $customGroup['table_name'] . ' WHERE ' . $customField['column_name'] . ' NOT LIKE "%Not Applicable%" AND ' . $customField['column_name'] . ' IS NOT NULL AND ' . $customField['column_name'] . ' <> ""';
      $dao = CRM_Core_DAO::executeQuery($query);
      $isCustomFieldUsed = $dao->fetchAll();
      if (!empty($isCustomFieldUsed)) {
        continue;
      }

      civicrm_api3('CustomField', 'delete', [
        'id' => $customField['id'],
      ]);
    }
  }

}
