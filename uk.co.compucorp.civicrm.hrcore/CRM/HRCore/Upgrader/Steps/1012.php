<?php

trait CRM_HRCore_Upgrader_Steps_1012 {
  
  /**
   * Adds a submenu containing links to edit contact related option groups,
   * and relationship types
   *
   * @return bool
   */
  public function upgrade_1012() {
    $domain = CRM_Core_Config::domainID();
    $params = ['return' => 'id', 'name' => 'Administer', 'domain_id' => $domain];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $this->up1012_addLocalisationShortcut($administerId);
    $this->up1012_addOtherStaffDetailsSubmenu($administerId);
    $this->up1012_addCustomFieldsShortcut($administerId);

    return TRUE;
  }

  /**
   * Adds a shortcut to the localization page
   *
   * @param int $administerId
   */
  private function up1012_addLocalisationShortcut($administerId) {
    $result = $this->up1012_createNavItem(
      'Localise CiviCRM',
      'access CiviCRM',
      $administerId,
      ['url' => 'civicrm/admin/setting/localization?reset=1']
    );

    // weight cannot be set when you're creating first time
    $id = $result['id'];
    civicrm_api3('Navigation', 'create', ['id' => $id, 'weight' => -101]);
  }

  /**
   * @param $administerId
   * @throws CiviCRM_API3_Exception
   */
  private function up1012_addOtherStaffDetailsSubmenu($administerId) {
    $permission = 'access CiviCRM';
    $parentName = 'Other Staff Details';
    $parent = $this->up1012_createNavItem($parentName, $permission, $administerId);
    $parentId = $parent['id'];

    // Weight cannot be set when creating for the first time
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -98]);

    // If we don't flush it will not recognize newly created parent_id
    CRM_Core_PseudoConstant::flush();

    $childLinks = [
      'Prefixes' =>
        $this->up1012_getOptionGroupLink('individual_prefix'),
      'Genders' =>
        $this->up1012_getOptionGroupLink('gender'),
      'Emergency Contact Relationships' =>
        $this->up1012_getOptionGroupLink('relationship_with_employee_20150304120408'),
      'Manager Types' =>
        'civicrm/admin/reltype?reset=1',
      'Career History' =>
        $this->up1012_getOptionGroupLink('occupation_type_20130617111138'),
      'Disability Types' =>
        $this->up1012_getOptionGroupLink('type_20130502151940'),
      'Qualifications – Skill Categories' =>
        $this->up1012_getOptionGroupLink('category_of_skill_20130510015438'),
      'Qualifications – Skill Levels' =>
        $this->up1012_getOptionGroupLink('level_of_skill_20130510015934'),
    ];

    foreach ($childLinks as $itemName => $link) {
      $params = ['url' => $link];
      $this->up1012_createNavItem($itemName, $permission, $parentId, $params);
    }
  }

  /**
   * Adds a shortcut to the edit custom groups + fields page
   *
   * @param int $administerId
   */
  private function up1012_addCustomFieldsShortcut($administerId) {
    $result = $this->up1012_createNavItem(
      'Custom Fields',
      'administer CiviCRM',
      $administerId,
      ['url' => 'civicrm/admin/custom/group?reset=1']
    );

    // weight cannot be set when you're creating first time
    $id = $result['id'];
    civicrm_api3('Navigation', 'create', ['id' => $id, 'weight' => -95]);
  }

  /**
   * Gets the link to edit an option group
   *
   * @param string $groupName
   *
   * @return string
   */
  private function up1012_getOptionGroupLink($groupName) {
    return 'civicrm/admin/options/' . $groupName . '?reset=1';
  }

  /**
   * Creates a navigation menu item using the API
   *
   * @param string $name
   * @param string $permission
   * @param int $parentID
   * @param array $params
   *
   * @return array
   */
  private function up1012_createNavItem(
    $name,
    $permission,
    $parentID,
    $params = []
  ) {
    $params = array_merge([
      'name' => $name,
      'label' => ts($name),
      'permission' => $permission,
      'parent_id' => $parentID,
      'is_active' => 1,
    ], $params);

    $existing = civicrm_api3('Navigation', 'get', $params);

    if ($existing['count'] > 0) {
      return array_shift($existing['values']);
    }

    return civicrm_api3('Navigation', 'create', $params);
  }
}
