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
    if ($otherId == null) {
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
   * Creates an option group for organisation provider
   * used for job role funder
   *
   * @return bool
   */
  public function upgrade_1007() {
    $result = civicrm_api3('OptionGroup', 'get', [
      'name' => 'hrjc_funder',
    ]);

    if ($result['count'] === 0) {
      $file = 'xml/option_groups/organisation_provider_install.xml';
      $this->executeCustomDataFile($file);
    }

    return TRUE;
  }

  /**
   * Migrates existing job role funder to option values
   *
   * @return bool
   */
  public function upgrade_1008() {
    $result = civicrm_api3('HrJobRoles', 'get', [
      'return' => ['funder'],
    ]);
    $jobRoles = $result['values'];
    $funderIds = $this->getFunderIds($jobRoles);

    if (count($funderIds) === 0) {
      return TRUE;
    }

    $contactFunders = $this->createFunderOptionValues($funderIds);
    $this->updateJobRoleFunder($jobRoles, $contactFunders);

    return TRUE;
  }

  /**
   * Swaps job role funder contact id with option values and updates record
   *
   * @param array $jobRoles
   * @param array $contactFunders
   */
  private function updateJobRoleFunder($jobRoles, $contactFunders) {
    foreach ($jobRoles as $jobRole) {
      foreach ($contactFunders as $contactId => $optionValue) {
        $jobRole['funder'] = str_replace(
          '|' . $contactId . '|',
          '|' . $optionValue['value'] . '|',
          $jobRole['funder']
        );
      }

      civicrm_api3('HrJobRoles', 'create', [
        'id' => $jobRole['id'],
        'funder' => $jobRole['funder']
      ]);
    }
  }

  /**
   * Sets up option values for job role funders
   * using hrjc_funder option group
   *
   * @param array $funderIds
   *
   * @return array
   */
  private function createFunderOptionValues($funderIds) {
    $funderOptionValueIds = [];
    $contactResult = civicrm_api3('Contact', 'get', [
      'return' => ['sort_name', 'display_name'],
      'id' => ['IN' => $funderIds],
    ]);

    $contacts = $contactResult['values'];
    foreach ($contacts as $contact) {
      $funderOptionValueIds[$contact['id']] = CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'hrjc_funder',
        'name' => $contact['display_name'],
        'label' => $contact['sort_name']
      ]);
    }

    return $funderOptionValueIds;
  }

  /**
   * Retrieves unique id of contacts used as funder
   *
   * @param array $jobRoles
   *
   * @return array
   */
  private function getFunderIds($jobRoles) {
    $funderIds = [];
    foreach ($jobRoles as $jobRole) {
      $funders = array_values(array_filter(explode('|', $jobRole['funder'])));
      $funderIds = array_merge($funderIds, $funders);
    }

    return array_unique($funderIds);
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

    return isset($result['id']) ? $result['id'] : null;
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
