<?php

trait CRM_HRUI_Upgrader_Steps_4705 {

  /**
   * Upgrader to fix Social Account Type (formerly Website Type) option group,
   * which got deactivated on upgrade 4702.
   *
   * @return bool
   */
  public function upgrade_4705() {
    civicrm_api3('OptionGroup', 'get', [
      'name' => 'website_type',
      'api.OptionGroup.create' => [
        'id' => '$value.id',
        'is_active' => 1,
      ],
    ]);

    return TRUE;
  }
}
