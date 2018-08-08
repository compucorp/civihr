<?php

trait CRM_HRCore_Upgrader_Steps_1029 {

  /**
   * Deletes Default Groups
   *
   * @return bool
   */
  public function upgrade_1029() {
    $this->up1029_deleteDefaultGroups([
      'Advisory board',
      'Newsletter Subscribers',
      'Summer Program Volunteers',
      'Administrators',
    ]);

    $this->up1029_disableAndHideDefaultGroup('Case_Resources');

    return TRUE;
  }

  /**
   * Deletes the Groups passed by params
   *
   * @param array $groupsToDelete
   */
  private function up1029_deleteDefaultGroups($groupsToDelete) {
    $groups = civicrm_api3('Group', 'get', [
      'name' => ['IN' => $groupsToDelete],
      'api.Group.delete' => ['id' => '$value.id'],
    ]);
  }

  /**
   * Disable and Hide Groups Passed By Params
   *
   * @param string $groupToHide
   */
  private function up1029_disableAndHideDefaultGroup($groupToHide) {
    $group = civicrm_api3('Group', 'get', [
      'name' => $groupToHide,
      'api.Group.create' => [
        'id' => '$value.id',
        'is_hidden' => 1,
        'is_active' => 0,
      ],
    ]);
  }

}
