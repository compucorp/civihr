<?php

class CRM_HRCore_HookListener_EventBased_OnAlterMenu extends CRM_HRCore_HookListener_BaseListener {

  public function handle(&$items) {
    $items['civicrm/api']['access_arguments'] =[['access CiviCRM', 'access CiviCRM developer menu and tools'], "and"];
    $items['civicrm/styleguide']['access_arguments'] =[['access CiviCRM', 'access CiviCRM developer menu and tools'], "and"];
  }
}
