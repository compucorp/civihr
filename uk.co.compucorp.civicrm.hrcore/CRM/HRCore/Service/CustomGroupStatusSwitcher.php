<?php

class CRM_HRCore_Service_CustomGroupStatusSwitcher {

  /**
   * Enable a custom group and all its fields
   *
   * @param string $customGroupName
   */
  public function enable($customGroupName) {
    $this->changeStatus($customGroupName, TRUE);
  }

  /**
   * Disable a custom group and all its fields
   *
   * @param string $customGroupName
   */
  public function disable($customGroupName) {
    $this->changeStatus($customGroupName, FALSE);
  }

  /**
   * Switches the status for custom group and all its fields
   *
   * @param string $customGroupName
   * @param bool $status
   */
  private function changeStatus($customGroupName, $status) {
    $customGroup = $this->getCustomGroup($customGroupName);

    if (!$customGroup) {
      return;
    }

    $customGroupId = (int) $customGroup['id'];
    $this->changeGroupStatus($customGroupId, $status);
    $this->changeAllGroupFieldsStatus($customGroupId, $status);
  }

  /**
   * Update the 'is_active' status for a custom group
   *
   * @param int $customGroupId
   * @param bool $status
   */
  private function changeGroupStatus($customGroupId, $status) {
    $params = ['id' => $customGroupId, 'is_active' => $status];
    civicrm_api3('CustomGroup', 'create', $params);
  }

  /**
   * Update the 'is_active' status for all custom fields for a group
   *
   * @param int $customGroupId
   * @param bool $status
   */
  private function changeAllGroupFieldsStatus($customGroupId, $status) {
    $fields = $this->getAllCustomFields($customGroupId);

    foreach ($fields as $field) {
      $params = ['id' => $field['id'], 'is_active' => $status];
      civicrm_api3('CustomField', 'create', $params);
    }
  }

  /**
   * Fetches all custom fields for a custom group
   *
   * @param int $customGroupId
   *
   * @return array
   */
  private function getAllCustomFields($customGroupId) {
    $params = ['custom_group_id' => $customGroupId];
    $result = civicrm_api3('CustomField', 'get', $params);

    return $result['values'];
  }

  /**
   * Gets a custom group based on name, returns null if group doesn't exist
   *
   * @param string $customGroupName
   *
   * @return mixed|null
   */
  private function getCustomGroup($customGroupName) {
    $params = ['name' => $customGroupName];
    $result = civicrm_api3('CustomGroup', 'get', $params);

    if ($result['count'] != 1) {
      return NULL;
    }

    return array_shift($result['values']);
  }

}
