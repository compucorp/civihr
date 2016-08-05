<?php

class CRM_Hrjobroles_BAO_HrJobRoles extends CRM_Hrjobroles_DAO_HrJobRoles {


  /**
   * Create a new HrJobRoles based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Activity_DAO_HrJobRoles|NULL
   */
  public static function create($params) {
    $entityName = 'HrJobRoles';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new static();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Return an associative array with Roles for specific contact.
   * @param int $cuid
   * @return array
   */
  public static function getContactRoles($cuid)  {
    $queryParam = array(1 => array($cuid, 'String'));
    $query = "SELECT cjr.id as role_id, cjr.job_contract_id as contract_id, cjr.title
              FROM civicrm_hrjobroles cjr
              left join civicrm_hrjobcontract cjc on cjr.job_contract_id=cjc.id
              where cjc.contact_id=%1 and cjc.deleted = 0;";
    $counter = 0;
    $roles = CRM_Core_DAO::executeQuery($query, $queryParam);
    $rolesList = array();
    while ($roles->fetch()) {
      $roleDetails['role_id'] = $roles->role_id;
      $roleDetails['contract_id'] = $roles->contract_id;
      $roleDetails['title'] = $roles->title;
      $rolesList[$counter++] = $roleDetails;
    }

    return $rolesList;
  }

  /**
   * Check Contact if exist   .
   *
   * @param String $searchValue
   * @param String $searchField
   * @return Integer ( Contact ID or 0 if not exist)
   */
  public static function contactExists($searchValue, $searchField) {
    $queryParam = array(1 => array($searchValue, 'String'));
    $query = "SELECT id from civicrm_contact where ".$searchField." = %1";
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    return $result->fetch() ? $result->id : 0;
  }

  /**
   * Get option values for specific option group.
   *
   * @param String $fieldName
   *
   * @return array
   */
  public static function buildDbOptions($fieldName) {
    $queryParam = array(1 => array($fieldName, 'String'));
    $query = "SELECT cpv.value, cpv.label from civicrm_option_value cpv
              LEFT JOIN civicrm_option_group cpg on cpv.option_group_id = cpg.id
              WHERE cpg.name = %1";
    $options = [];
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    while ($result->fetch()) {
      $options[$result->value] = strtolower($result->label);
    }
    return $options;
  }

  public static function importableFields() {
    $fields = array('' => array('title' => ts('- do not import -')));
    return array_merge($fields, static::import());
  }


}
