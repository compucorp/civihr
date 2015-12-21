<?php

class CRM_Contactaccessrights_Utils_ACL {
  /**
   * @var int
   */
  private $contactId;

  /***
   * @var array
   */
  private $whereTables = [];

  /**
   * @var array
   */
  private $whereConditions = [];

  public function __construct($contactId = NULL) {
    $this->contactId = $contactId ?: CRM_Core_Session::singleton()->get('userID');

    $this->addJobContractClause()
      ->addJobContractRevClause()
      ->addJobContractDetailsClause()
      ->addJobRolesClause();
  }

  public function getWhereTables($whereTables = []) {
    return $this->whereTables;
  }

  public function getWhereConditions($whereConditions = '') {
    return $this->whereConditions;
  }

  private function addJobContractClause() {
    $this->addWhereTable('1', "LEFT JOIN civicrm_hrjobcontract jc ON contact_a.id = jc.contact_id");

    // Disabling the where condition as not sure if we need to limit access based on primary contracts only
    // $this->addWhereCondition('jc.is_primary = 1');

    return $this;
  }

  private function addJobContractRevClause() {
    $this->addWhereTable(
      '2',
      "LEFT JOIN civicrm_hrjobcontract_revision jcr ON jc.id = jcr.jobcontract_id"
    );
    $this->addWhereCondition('jcr.effective_date <= NOW()');

    return $this;
  }

  private function addJobContractDetailsClause() {
    $this->addWhereTable(
      '3',
      'LEFT JOIN civicrm_hrjobcontract_details jcd ON jcr.id = jcd.jobcontract_revision_id'
    );
    $this->addWhereCondition('(jcd.period_end_date >= NOW() OR jcd.period_end_date IS NULL)');

    return $this;
  }

  private function addJobRolesClause() {
    $locationIds = array_column($this->getLocations(), 'id') ?: [0];
    $regionIds = array_column($this->getRegions(), 'id') ?: [0];

    $this->addWhereTable(
      '4',
      'LEFT JOIN civicrm_hrjobroles jr ON jc.id = jr.job_contract_id'
    );
    $this->addWhereCondition('jr.location IN (' . implode(', ', $locationIds) . ')');

    return $this;
  }

  private function addWhereTable($table, $clause) {
    $this->whereTables[$table] = $clause;
  }

  private function addWhereCondition($condition) {
    $this->whereConditions[] = $condition;
  }

  private function getLocations() {
    try {
      $locations = civicrm_api3('Rights', 'getlocations', ['sequential' => 1, 'contact_id' => $this->contactId]);

      return $locations['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }

  private function getRegions() {
    try {
      $regions = civicrm_api3('Rights', 'getregions', ['sequential' => 1, 'contact_id' => $this->contactId]);

      return $regions['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }
}
