<?php

class CRM_HRCore_HookListener_EventBased_OnNavigationMenu extends CRM_HRCore_HookListener_BaseListener {

  private $menu;

  public function handle(&$menu) {
    $this->menu = &$menu;

    $this->customImportMenuItems();
    $this->coreMenuChanges();
    $this->createHelpMenu();
    $this->createDeveloperMenu();
    $this->setDynamicMenuIcons();
    $this->renameMenuLabel('Contacts', 'Staff');
    $this->renameMenuLabel('Administer', 'Configure');
    $this->addSSPMenuItem();
  }

  /**
   * Generating Custom Fields import child menu items
   *
   */
  private function customImportMenuItems() {
    $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");

    $customFieldsNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'import_custom_fields', 'id', 'name');
    $contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');

    if ($customFieldsNavId) {
      // Degrade gracefully on 4.4
      if (is_callable(array('CRM_Core_BAO_CustomGroup', 'getMultipleFieldGroup'))) {
        //  Get the maximum key of $params
        $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();

        $multiValuedData = NULL;
        foreach ($multipleCustomData as $key => $value) {
          ++$navId;
          $multiValuedData[$navId] = array (
            'attributes' => array (
              'label'      => $value,
              'name'       => $value,
              'url'        => 'civicrm/import/custom?reset=1&id='.$key,
              'permission' => 'access CiviCRM',
              'operator'   => null,
              'separator'  => null,
              'parentID'   => $customFieldsNavId,
              'navID'      => $navId,
              'active'     => 1
            )
          );
        }
        $this->menu[$contactNavId]['child'][$customFieldsNavId]['child'] = $multiValuedData;
      }
    }
  }

  /**
   * Changes to some core menu items
   *
   */
  private function coreMenuChanges() {
    // remove search items
    $searchNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Search...', 'id', 'name');
    $toRemove = [
      'Full-text search',
      'Search builder',
      'Custom searches',
      'Find Cases',
      'Find Activities',
    ];
    foreach($toRemove as $item) {
      if (
        in_array($item, ['Find Cases', 'Find Activities'])
        && !($this->isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments'))
      ) {
        continue;
      }
      $itemId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $item , 'id', 'name');
      unset($this->menu[$searchNavId]['child'][$itemId]);
    }

    // remove contact items
    $searchNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
    $toRemove = [
      'New Tag',
      'Manage Tags (Categories)',
      'New Activity',
      'Import Activities',
      'Contact Reports',
    ];
    foreach($toRemove as $item) {
      if (
        in_array($item, ['New Activity', 'Import Activities'])
        && !($this->isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments'))
      ) {
        continue;
      }
      $itemId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $item , 'id', 'name');
      unset($this->menu[$searchNavId]['child'][$itemId]);
    }

    // remove main Reports menu
    $reportsNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
    unset($this->menu[$reportsNavId]);

    // Remove Admin items
    $adminNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');

    $civiReportNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'CiviReport', 'id', 'name');

    $civiCaseNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'CiviCase', 'id', 'name');
    $redactionRulesNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Redaction Rules', 'id', 'name');
    $supportNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Support', 'id', 'name');

    unset($this->menu[$supportNavId]);
    unset($this->menu[$adminNavId]['child'][$civiReportNavId]);
    unset($this->menu[$adminNavId]['child'][$civiCaseNavId]['child'][$redactionRulesNavId]);
  }

  /**
   * Creates Help Menu in navigation bar
   *
   */
  private function createHelpMenu() {
    _hrcore_civix_insert_navigation_menu($this->menu, '', [
      'name' => ts('Help'),
      'permission' => 'access CiviCRM',
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Help', [
      'name' => ts('User Guide'),
      'url' => 'http://civihr-documentation.readthedocs.io/en/latest/',
      'target' => '_blank',
      'permission' => 'access CiviCRM'
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Help', [
      'name' => ts('CiviHR website'),
      'url' => 'https://www.civihr.org/',
      'target' => '_blank',
      'permission' => 'access CiviCRM'
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Help', [
      'name' => ts('Get support'),
      'url' => 'https://www.civihr.org/support',
      'target' => '_blank',
      'permission' => 'access CiviCRM'
    ]);
  }

  /**
   * Creates Developer Menu in navigation bar
   *
   */
  private function createDeveloperMenu() {
    _hrcore_civix_insert_navigation_menu($this->menu, '', [
      'name' => ts('Developer'),
      'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
      'operator' => 'AND'
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Developer', [
      'name' => ts('API Explorer'),
      'url' => 'civicrm/api',
      'target' => '_blank',
      'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
      'operator' => 'AND'
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Developer', [
      'name' => ts('Developer Docs'),
      'target' => '_blank',
      'url' => 'https://civihr.atlassian.net/wiki/spaces/CIV/pages',
      'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
      'operator' => 'AND'
    ]);

    _hrcore_civix_insert_navigation_menu($this->menu, 'Developer', [
      'name' => ts('Style Guide'),
      'target' => '_blank',
      'url' => 'https://www.civihr.org/support',
      'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
      'operator' => 'AND'
    ]);

    // Adds sub menu under Style Guide menu
    foreach (Civi::service('style_guides')->getAll() as $styleGuide) {
      _hrcore_civix_insert_navigation_menu($this->menu, 'Developer/Style Guide', [
        'label' => $styleGuide['label'],
        'name' => $styleGuide['name'],
        'url' => 'civicrm/styleguide/' . $styleGuide['name'],
        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
        'operator' => 'AND'
      ]);
    }
  }

  /**
   * Adds icons to dynamically defined menu items
   *
   */
  private function setDynamicMenuIcons() {
    $menuToIcons = [
      'Help' => 'fa fa-question-circle',
      'Developer'=> 'fa fa-code',
    ];

    foreach ($this->menu as $key => $item) {
      $menuName = $item['attributes']['name'];
      if (array_key_exists($menuName, $menuToIcons)) {
        $this->menu[$key]['attributes']['icon'] = $menuToIcons[$menuName];
      }
    }
  }

  /**
   * Renames a menu with the given new label
   *
   * @param string $menuName
   * @param string $newLabel
   */
  private function renameMenuLabel($menuName, $newLabel) {
    $menuItemID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $menuName, 'id', 'name');
    $this->menu[$menuItemID]['attributes']['label'] = $newLabel;
  }

  /**
   * Adds a "Self Service Portal" menu item
   *
   */
  private function addSSPMenuItem() {
    _hrcore_civix_insert_navigation_menu($this->menu, '', [
      'name' => ts('ssp'),
      'label' => ts('Self Service Portal'),
      'url' => 'dashboard',
    ]);
  }
}
