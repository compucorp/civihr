<?php

/**
 * Class CRM_Contactaccessrights_Utils_ACL
 *
 * A helper class for building ACL `WHERE` clause for controlling access to contacts, based on locations and regions a
 * contact has access to.
 */
class CRM_Contactaccessrights_Utils_ACL {
  /**
   * @var int Contact ID for the contact for whom permission check needs to be made.
   */
  private $contactId;

  /***
   * @var array Tables (to be joined) for ACL.
   */
  private $whereTables = [];

  /**
   * @var array Conditions for the `WHERE` clause for ACL.
   */
  private $whereConditions = [];

  /**
   * CRM_Contactaccessrights_Utils_ACL constructor.
   *
   * @param null $contactId
   */
  public function __construct($contactId = NULL) {
    $this->contactId = $contactId ?: CRM_Core_Session::singleton()->get('userID');

    $this->addJobContractClause()
      ->addJobContractRevClause()
      ->addJobContractDetailsClause()
      ->addJobRolesClause();
  }

  /**
   * Get an array of tables (to be joined) for ACL.
   *
   * @param array $whereTables
   *
   * @return array
   */
  public function getWhereTables($whereTables = []) {
    return $this->whereTables;
  }

  /**
   * Get an array of conditions for the `WHERE` clause for ACL.
   *
   * @param string $whereConditions
   *
   * @return array
   */
  public function getWhereConditions($whereConditions = '') {
    return $this->whereConditions;
  }

  /**
   * Add the relevant table(s) and conditions (i.e. where clause) for job contracts.
   *
   * @return $this
   */
  private function addJobContractClause() {
    $this->addWhereTable('car_0_jc', "INNER JOIN civicrm_hrjobcontract car_jc ON contact_a.id = car_jc.contact_id");

    return $this;
  }

  /**
   * Add the relevant table(s) and conditions (i.e. where clause) for job contract revisions.
   *
   * @return $this
   */
  private function addJobContractRevClause() {
    $this->addWhereTable(
      'car_1_jcr',
      "INNER JOIN civicrm_hrjobcontract_revision car_jcr ON (car_jc.id = car_jcr.jobcontract_id AND car_jcr.effective_date <= NOW())"
    );

    return $this;
  }

  /**
   * Add the relevant table(s) and conditions (i.e. where clause) for job contract details.
   *
   * @return $this
   */
  private function addJobContractDetailsClause() {
    $this->addWhereTable(
      'car_2_jcd',
      'INNER JOIN civicrm_hrjobcontract_details car_jcd ON (
        car_jcr.id = car_jcd.jobcontract_revision_id AND (car_jcd.period_end_date >= NOW() OR car_jcd.period_end_date IS NULL)
      )'
    );

    return $this;
  }

  /**
   * Add the relevant table(s) and conditions (i.e. where clause) for job roles.
   *
   * @return $this
   * @throws \CRM_Extension_Exception
   */
  private function addJobRolesClause() {
    $locationIds = "'" . implode("', '", array_column($this->getLocations(), 'value') ?: [0]) . "'";
    $regionIds = "'" . implode("', '", array_column($this->getRegions(), 'value') ?: [0]) . "'";

    $this->addWhereTable(
      'car_3_jr',
      "INNER JOIN civicrm_hrjobroles car_jr ON (
        car_jc.id = car_jr.job_contract_id AND (car_jr.location IN ({$locationIds}) OR car_jr.region IN ({$regionIds})))"
    );

    return $this;
  }

  /**
   * Add a table (to be joined) for ACL.
   *
   * @param $table
   * @param $clause
   */
  private function addWhereTable($table, $clause) {
    $this->whereTables[$table] = $clause;
  }

  /**
   * Add a condition for the `WHERE` clause for ACL.
   *
   * @param $condition
   */
  private function addWhereCondition($condition) {
    $this->whereConditions[] = $condition;
  }

  /**
   * Helper method for returning a list of locations accessible to the user in question.
   *
   * @return mixed
   * @throws \CRM_Extension_Exception
   */
  private function getLocations() {
    try {
      $locations = civicrm_api3('Rights', 'getlocations', ['sequential' => 1, 'contact_id' => $this->contactId]);

      return $locations['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }

  /**
   * Helper method for returning a list of regions accessible to the user in question.
   *
   * @return mixed
   * @throws \CRM_Extension_Exception
   */
  private function getRegions() {
    try {
      $regions = civicrm_api3('Rights', 'getregions', ['sequential' => 1, 'contact_id' => $this->contactId]);

      return $regions['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }
}
