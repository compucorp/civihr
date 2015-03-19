<?php

class CRM_Hrjobcontract_BAO_HRJobContractRevision extends CRM_Hrjobcontract_DAO_HRJobContractRevision {

  static $_importableFields = array();  
  
  /**
   * Create a new HRJobContractRevision based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobContractRevision|NULL
   *
   */
  public static function create($params) {
    global $user;
    
    $params['editor_uid'] = $user->uid;
    $className = 'CRM_Hrjobcontract_DAO_HRJobContractRevision';
    $entityName = 'HRJobContractRevision';
    $hook = empty($params['id']) ? 'create' : 'edit';
    
    $now = CRM_Utils_Date::currentDBDate();
    if ($hook === 'create')
    {
        $params['created_date'] = $now;
        $params['deleted'] = 0;
    }
    else
    {
        $params['modified_date'] = $now;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  
  static function importableFields($contactType = 'HRJobContractRevision',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobContractRevision';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobContractRevision::import();

      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobContractRevision::import());

      //Sorting fields in alphabetical order(CRM-1507)
      $fields = CRM_Utils_Array::crmArraySortByField($fields, 'title');
      $fields = CRM_Utils_Array::index(array('name'), $fields);

      CRM_Core_BAO_Cache::setItem($fields, 'contact fields', $cacheKeyString);
     }

    self::$_importableFields[$cacheKeyString] = $fields;

    if (!$isProfile) {
        $fields = array_merge(array('do_not_import' => array('title' => ts('- do not import -'))),
          self::$_importableFields[$cacheKeyString]
        );
    }
    return $fields;
  }
  
}
