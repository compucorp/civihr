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
   * Updates job role having wrong funder id with correct contact id.
   *
   * The wrong funder ids were as a result of contact api not returning
   * correct contact id. When requesting for list of individual contact
   * types to be used as funders, the api returns job role id instead of
   * contact id whenever a contacts have existing job role.
   *
   * To decide the correct id, the funders in each job roles are checked
   * against contacts whose contact types are individual. The id of contacts,
   * whose job role id matches the funder are used to update the previously
   * saved funder.
   *
   * @return bool
   * @throws CiviCRM_API3_Exception
   */
  public function upgrade_1007() {
    $contactDetails = $this->getContactsWithJobRole();
    $jobRoles = civicrm_api3('HrJobRoles', 'get', [
      'funder' => ['!=' => '|'],
    ]);

    foreach($jobRoles['values'] as $key => $value) {
      $funders = explode('|', $value['funder']);
      $funders = array_filter($funders);

      list($funders, $allowUpdate) = $this->changeFunderToCorrectContactId($funders, $contactDetails);
      if ($allowUpdate) {
        civicrm_api3('HrJobRoles', 'create', [
          'id' => $key,
          'funder' => '|' . implode('|', $funders) . '|',
        ]);
      }
    }

    return TRUE;
  }

  /**
   * Retrieves the correct contact for a funder and
   * indicates update is required when found
   *
   * @param array $funders
   * @param array $contactDetails
   *
   * @return array
   */
  private function changeFunderToCorrectContactId($funders, $contactDetails) {
    $allowUpdate = FALSE;
    foreach ($funders as $index => $funder) {
      $jobRoleIds = array_column($contactDetails, 'jobrole_id');
      $funderIndex = array_search($funder, $jobRoleIds);
      if ($funderIndex !== FALSE) {
        $funders[$index] = $contactDetails[$funderIndex]['id'];
        $allowUpdate = TRUE;
      }
    }

    return [$funders, $allowUpdate];
  }

  /**
   * Retrieves contacts with their job contract and job role
   *
   * @return array
   */
  private function getContactsWithJobRole() {
    $query = "
      SELECT c.id, jc.id as contract_id, jr.id AS jobrole_id, jr.funder
      FROM `civicrm_contact` c
      INNER JOIN civicrm_hrjobcontract jc ON (c.id = jc.contact_id)
      LEFT JOIN `civicrm_hrjobroles` jr ON (jc.id = jr.job_contract_id)
      WHERE c.contact_type = 'Individual'
    ";

    $result = CRM_Core_DAO::executeQuery($query);
    return $result->fetchAll();
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
