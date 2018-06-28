<?php

/**
 * Collection of upgrade steps
 */
class CRM_Hrjobroles_Upgrader extends CRM_Hrjobroles_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
//    $this->executeSqlFile('sql/myinstall.sql');
    $this->installCostCentreTypes();
  }

  /**
   * Example: Add start and and date for job roles
   *
   * @return TRUE on success
   * @throws Exception
   *
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update for job role start and end dates');
    CRM_Core_DAO::executeQuery("ALTER TABLE  `civicrm_hrjobroles` ADD COLUMN  `start_date` timestamp DEFAULT 0 COMMENT 'Start Date of the job role'");
    CRM_Core_DAO::executeQuery("ALTER TABLE  `civicrm_hrjobroles` ADD COLUMN  `end_date` timestamp DEFAULT 0 COMMENT 'End Date of the job role'");
    return TRUE;
  }

  public function upgrade_1002(){
    $this->installCostCentreTypes();

    return TRUE;
  }

  public function upgrade_1004() {
    CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_hrjobroles` MODIFY COLUMN `start_date` DATETIME DEFAULT NULL');
    CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_hrjobroles` MODIFY COLUMN `end_date` DATETIME DEFAULT NULL');
    //Update any end_date using "0000-00-00 00:00:00" as an empty value to NULL
    CRM_Core_DAO::executeQuery("UPDATE `civicrm_hrjobroles` SET `end_date` = NULL WHERE end_date = '0000-00-00 00:00:00'");

    return TRUE;
  }

  /**
   * Adds a submenu containing links to edit job role option groups
   *
   * @return bool
   */
  public function upgrade_1005() {
    $domain = CRM_Core_Config::domainID();
    $params = ['return' => 'id', 'name' => 'Administer', 'domain_id' => $domain];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $permission = 'access CiviCRM';
    $parent = $this->createNavItem('Job Roles', $permission, $administerId);
    $parentId = $parent['id'];

    // Weight cannot be set when creating for the first time
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -99]);

    // If we don't flush it will not recognize newly created parent_id
    CRM_Core_PseudoConstant::flush();

    $optionGroupLinks = [
      'Locations' => 'hrjc_location',
      'Regions' => 'hrjc_region',
      'Departments' => 'hrjc_department',
      'Levels' => 'hrjc_level_type',
      'Cost Centres' => 'cost_centres',
    ];

    foreach ($optionGroupLinks as $itemName => $optionGroup) {
      $link = 'civicrm/admin/options/' . $optionGroup . '?reset=1';
      $this->createNavItem($itemName, $permission, $parentId, ['url' => $link]);
    }

    return TRUE;
  }
  
  /**
   * Deletes cost centre "other" option value or disable it if not in use
   *
   * @return bool
   */
  public function upgrade_1006() {
    $jobRoles = civicrm_api3('HrJobRoles', 'get');
    if ($jobRoles['count'] == 0) {
      $this->deleteCostCentreOther();
    }
    
    $otherId = $this->retrieveCostCentreOtherId();
    if ($otherId == null) {
      return FALSE;
    }
    $inUse = FALSE;
    $roles = $jobRoles['values'];
    $pattern = '/\|' . $otherId . '\|/';
    foreach ($roles as $role) {
      if (preg_match($pattern, $role['cost_center'])) {
        $inUse = TRUE;
        break;
      }
    }
    
    if (!$inUse) { // disables cost centre other
      civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'cost_centres',
        'name' => 'Other',
        'api.OptionValue.create' => ['id' => '$value.id', 'is_active' => 0],
      ]);
    }
    
    return TRUE;
  }
  
  /**
   * Deletes cost centre option value with name "other"
   */
  private function deleteCostCentreOther() {
    civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cost_centres',
      'name' => 'Other',
      'api.OptionValue.delete' => ['id' => '$value.id'],
    ]);
  }
  
  /**
   * Fetches the id of cost center "other" option value
   *
   * @return int
   */
  private function retrieveCostCentreOtherId() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cost_centres',
      'name' => 'Other',
    ]);
    
    return ($result['id'])? $result['id'] : null;
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
  private function createNavItem($name, $permission, $parentID, $params = []) {
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

  /**
   * Creates new Option Group for Cost Centres
   */
  public function installCostCentreTypes() {
    try{
      $result = civicrm_api3('OptionGroup', 'create', [
          'sequential' => 1,
          'name' => "cost_centres",
          'title' => "Cost Centres",
          'is_active' => 1
      ]);

      $id = $result['id'];

      $val = 'Other';
      civicrm_api3('OptionValue', 'create', [
        'sequential' => 1,
        'option_group_id' => $id,
        'label' => $val,
        'value' => $val,
        'name' => $val,
      ]);

    } catch(Exception $e){
      // OptionGroup already exists
      // Skip this
    }
  }
}
