<?php

trait CRM_Hremergency_Upgrader_Steps_1004 {

  /**
   * Upgrade CustomGroup, setting Emergency_Contacts is_reserved to Yes.
   * Title was included here because of an issue with webform_civicrm
   * @see https://www.drupal.org/project/webform_civicrm/issues/2947922
   * 
   * @return bool
   */
  public function upgrade_1004() {
    $creationParams = [
      'id' => '\$value.id',
      'is_reserved' => 1,
      'title' => 'Emergency Contacts'
    ];
    
    civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'name' => 'Emergency_Contacts',
      'api.CustomGroup.create' => $creationParams,
    ]);

    return TRUE;
  }

}
