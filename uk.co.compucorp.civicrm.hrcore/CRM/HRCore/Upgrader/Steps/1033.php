<?php

trait CRM_HRCore_Upgrader_Steps_1033 {

  /**
   * Updates menu permissions
   *
   * @return bool
   */
  public function upgrade_1033() {
    $permissions = [
      'access root menu items and configurations',
      'edit system workflow message templates',
      'edit user-driven message templates'
    ];

    $this->up1033_updateMessageTemplatePermissions($permissions);
    $this->up1033_updateCommunicationsPermissions($permissions);

    return TRUE;
  }

  /**
   * Updates message template permissions
   *
   * @param array $permissions
   */
  private function up1033_updateMessageTemplatePermissions($permissions) {
    civicrm_api3('Navigation', 'get', [
      'parent_id' => 'Communications',
      'url' => 'civicrm/admin/messageTemplates?reset=1',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'permission' => implode(',', $permissions),
        'permission_operator' => 'OR'
      ],
    ]);
  }

  /**
   * Updates communications permissions
   *
   * @param array $permissions
   */
  private function up1033_updateCommunicationsPermissions($permissions) {
    civicrm_api3('Navigation', 'get', [
      'name' => 'Communications',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'permission' => implode(',', $permissions),
        'permission_operator' => 'OR'
      ],
    ]);
  }
}
