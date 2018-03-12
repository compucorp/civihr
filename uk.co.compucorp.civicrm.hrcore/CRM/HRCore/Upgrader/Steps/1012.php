<?php

trait CRM_HRCore_Upgrader_Steps_1012 {

  /**
   * Adds a submenu containing links to edit contact related option groups,
   * and relationship types
   *
   * @return bool
   */
  public function upgrade_1012() {
    $params = ['return' => 'id', 'name' => 'Administer'];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $permission = 'Access CiviCRM';
    $parentName = 'Other Staff Details';
    $parent = $this->up1012_createNavItem($parentName, $permission, $administerId);
    $parentId = $parent['id'];

    // Weight cannot be set when creating for the first time
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -98]);

    // If we don't flush it will not recognize newly created parent_id
    CRM_Core_PseudoConstant::flush();

    $optionGroupLinks = [
      'Prefixes' => 'individual_prefix',
      'Emergency Contact Relationships' => 'relationship_with_employee_20150304120408',
      'Career History' => 'occupation_type_20130617111138',
      'Disability Types' => 'type_20130502151940',
      'Qualifications – Skill Categories' => 'category_of_skill_20130510015438',
      'Qualifications – Skill Levels' => 'level_of_skill_20130510015934',
    ];

    foreach ($optionGroupLinks as $itemName => $optionGroup) {
      $link = 'civicrm/admin/options/' . $optionGroup . '?reset=1';
      $params = ['url' => $link];
      $this->up1012_createNavItem($itemName, $permission, $parentId, $params);
    }

    return TRUE;
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

    return civicrm_api3('navigation', 'create', $params);
  }
}
