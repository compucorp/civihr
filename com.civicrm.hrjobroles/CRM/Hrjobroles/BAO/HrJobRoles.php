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
   * Get options for a given job roles field along with their database IDs.
   *
   * @param String $fieldName
   *
   * @return Array
   */
  public static function buildDbOptions($fieldName) {
    $queryParam = array(1 => array($fieldName, 'String'));
    $query = "SELECT cpv.id, cpv.label from civicrm_option_value cpv
              LEFT JOIN civicrm_option_group cpg on cpv.option_group_id = cpg.id
              WHERE cpg.name = %1";
    $options = array();
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    while ($result->fetch()) {
      $options[] =  array( 'id'=>$result->id, 'label'=>strtolower($result->label) );
    }
    return $options;
  }

  /**
   * Check Contact if exist   .
   *
   * @param String $searchValue
   * @param String $searchField
   * @return Integer ( Contact ID or 0 if not exist)
   */
  public static function checkContact($searchValue, $searchField) {
    $queryParam = array(1 => array($searchValue, 'String'));
    $query = "SELECT id from civicrm_contact where ".$searchField." = %1";
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    return $result->fetch() ? $result->id : 0;
  }


  /**
   * Check Job Contract if exist   .
   *
   * @param Integer $contractID
   * @return array|0 ( return 0 if not exist or an array contain some contract details if exist )
   */
  public static function checkContract($contractID) {
    if ( !CRM_Utils_Rule::positiveInteger($contractID))  {
      return 0;
    }
    $queryParam = array(1 => array($contractID, 'Integer'));
    $query = "SELECT chrjcd.period_start_date, chrjcd.period_end_date from civicrm_hrjobcontract_revision chrjcr
              left join civicrm_hrjobcontract_details chrjcd on chrjcr.id=chrjcd.jobcontract_revision_id
              where  chrjcr.jobcontract_id = %1 order by chrjcr.id desc limit 1";
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    return $result->fetch() ? $result : 0;
  }

  public static function importableFields() {
    $fields = array('' => array('title' => ts('- do not import -')));
    return array_merge($fields, static::import());
  }


}
