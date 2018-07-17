<?php

trait CRM_HRCore_Upgrader_Steps_1025 {

  /**
   * Deletes some demographics fields where they are not in use
   *
   * @return bool
   */
  public function upgrade_1025() {

    $this->up1025_deleteExtendDemographicFields([
      'Ethnicity',
      'Religion',
      'Sexual_Orientation',
    ]);

    return TRUE;
  }

  /**
   * Deletes Custom Demographic Fields only if they are not used
   *
   * @param array $fieldsToDelete
   */
  private function up1025_deleteExtendDemographicFields($fieldsToDelete) {
    $customGroup = civicrm_api3('CustomGroup', 'get', [
      'name' => 'Extended_Demographics',
    ]);
    $customGroup = array_shift($customGroup['values']);
    $customFields = civicrm_api3('CustomField', 'get', [
      'name' => ['IN' => $fieldsToDelete],
    ]);
    $tableName = $customGroup['table_name'];
    foreach ($customFields['values'] as $customField) {
      $column = $customField['column_name'];
      $queryFormat = 'SELECT COUNT(id) FROM %s'
        . ' WHERE %s NOT LIKE "%%Not Applicable%%"'
        . ' AND %s IS NOT NULL'
        . ' AND %s <> ""';

      $query = sprintf($queryFormat, $tableName, $column, $column, $column);
      $customFieldItems = CRM_Core_DAO::singleValueQuery($query);
      if ($customFieldItems > 0) {
        continue;
      }

      civicrm_api3('CustomField', 'delete', [
        'id' => $customField['id'],
      ]);
    }
  }

}
