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

  public function upgrade_1002() {
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
    $params = [
      'return' => 'id',
      'name' => 'Administer',
      'domain_id' => $domain,
    ];
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
   * Deletes cost centre "other" option value if not in use
   *
   * @return bool
   */
  public function upgrade_1006() {
    $jobRoles = civicrm_api3('HrJobRoles', 'get');
    if ($jobRoles['count'] == 0) {
      $this->deleteCostCentreOther();
      return TRUE;
    }

    $otherId = $this->retrieveCostCentreOtherId();
    if ($otherId == NULL) {
      return TRUE;
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

    if (!$inUse) {
      $this->deleteCostCentreOther();
    }

    return TRUE;
  }

  /**
   * Creates a new Import Job Roles Menu
   *
   * @return bool
   */
  public function upgrade_1007() {
    $leaveAndAbsensesMenu = civicrm_api3('Navigation', 'get', [
      'name' => 'leave_and_absences',
    ]);

    try {
      $importMenu = civicrm_api3('Navigation', 'getsingle', ['name' => 'Import']);
    } catch (CiviCRM_API3_Exception $e) {
      $leaveAndAbsensesMenu = array_shift($leaveAndAbsensesMenu['values']);
      $importMenu = civicrm_api3('Navigation', 'create', [
        'label' => 'Import',
        'name' => 'Import',
        'parent_id' => $leaveAndAbsensesMenu['parent_id'],
        'domain_id' => $leaveAndAbsensesMenu['domain_id'],
        'permission' => $leaveAndAbsensesMenu['permission'],
        'is_active' => 1,
        'weight' => $leaveAndAbsensesMenu['weight'],
      ]);
      CRM_Core_PseudoConstant::flush();
      $importMenu = array_shift($importMenu['values']);
    }

    try {
      civicrm_api3('Navigation', 'getsingle', ['name' => 'import_job_roles']);
    } catch (Exception $e) {
      civicrm_api3('Navigation', 'create', [
        'label' => 'Import Job Roles',
        'name' => 'import_job_roles',
        'parent_id' => $importMenu['id'],
        'domain_id' => $importMenu['domain_id'],
        'permission' => $importMenu['permission'],
        'url' => 'civicrm/jobroles/import',
        'is_active' => 1,
      ]);
      CRM_Core_PseudoConstant::flush();
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

    return isset($result['id']) ? $result['id'] : NULL;
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
    try {
      $result = civicrm_api3('OptionGroup', 'create', [
        'sequential' => 1,
        'name' => "cost_centres",
        'title' => "Cost Centres",
        'is_active' => 1,
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

    } catch (Exception $e) {
      // OptionGroup already exists
      // Skip this
    }
  }
}
