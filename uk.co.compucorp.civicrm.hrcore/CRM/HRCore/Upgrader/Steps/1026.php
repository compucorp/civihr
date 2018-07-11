<?php

trait CRM_HRCore_Upgrader_Steps_1026 {

  /**
   * Deletes Default Groups
   *
   * @return bool
   */
  public function upgrade_1026() {
    $this->up1026_deleteDefaultGroups([
      'Advisory board',
      'Newsletter Subscribers',
      'Summer Program Volunteers',
      'Administrators',
    ]);

    $this->up1026_disableAndHideDefaultGroup('Case_Resources');

    return TRUE;
  }

  /**
   * Deletes the Groups passed by params
   *
   * @param array $groupsToDelete
   */
  private function up1026_deleteDefaultGroups($groupsToDelete) {
    $groups = civicrm_api3('Group', 'get', [
      'name' => ['IN' => $groupsToDelete],
    ]);

    $groups = $groups['values'];
    foreach ($groups as $groupId => $group) {
      civicrm_api3('Group', 'delete', [
        'id' => $groupId,
      ]);
    }
  }

  /**
   * Disable and Hide Groups Passed By Params
   *
   * @param string $groupToHide
   */
  private function up1026_disableAndHideDefaultGroup($groupToHide) {
    $group = civicrm_api3('Group', 'get', [
      'name' => $groupToHide,
    ]);

    civicrm_api3('Group', 'create', [
      'id' => $group['id'],
      'is_hidden' => 1,
      'is_active' => 0,
    ]);
  }

}
