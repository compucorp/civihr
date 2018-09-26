<?php

class CRM_HRCore_Menu_Child_Developer {

  /**
   * Returns menu Items for Developer Menu.
   *
   * @return array
   */
  public static function getItems() {
    return [
      'API Explorer' =>
        [
          'url' => 'civicrm/api',
          'target' => '_blank',
          'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
          'operator' => 'AND',
        ],
      'Developer Docs' =>
        [
          'target' => '_blank',
          'url' => 'https://civihr.atlassian.net/wiki/spaces/CIV/pages',
          'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
          'operator' => 'AND',
        ],
      'Style Guide' =>
        [
          'target' => '_blank',
          'url' => 'https://www.civihr.org/support',
          'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
          'operator' => 'AND',
          'children' => CRM_HRCore_Menu_Child_StyleGuide::getItems(),
        ],
    ];
  }
}
