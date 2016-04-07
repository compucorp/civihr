<?php

class CRM_Hrjobcontract_BAO_HRJobContractRevision extends CRM_Hrjobcontract_DAO_HRJobContractRevision {

  static $_importableFields = array();  
  
  /**
   * Create a new HRJobContractRevision based on array-data
   *
   * @param array $params key-value pairs
   * 
   * @return CRM_HRJobContract_DAO_HRJobContractRevision|NULL
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

    if (empty($params['jobcontract_id']) && $hook === 'edit') {
        $revision = new $className();
        $revision->id = $params['id'];
        $revision->find(TRUE);
        $params['jobcontract_id'] = $revision->jobcontract_id;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    if (!empty($instance->jobcontract_id)) {
      self::updateEffectiveEndDates($instance->jobcontract_id);
    }
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Update Revision's effective end dates for given Job Contract ID.
   * 
   * @param int $jobcontractId
   */
  public static function updateEffectiveEndDates($jobcontractId) {
    $query = "SELECT id, effective_date FROM civicrm_hrjobcontract_revision " .
             "WHERE jobcontract_id = %1 " .
             "AND deleted = 0 " .
             "ORDER BY effective_date DESC, id DESC";
    $params = array(
      1 => array($jobcontractId, 'Integer'),
    );
    $revisions = CRM_Core_DAO::executeQuery($query, $params);
    $previousEffectiveDate = null;
    // Updating 'effective_end_date' of each revision by 'effective_date' - 1 DAY
    // of the next (newer) revision.
    while ($revisions->fetch()) {
      if ($previousEffectiveDate) {
        $effectiveEndDate = $revisions->effective_date;
        if ($previousEffectiveDate !== $revisions->effective_date) {
          $effectiveEndDate = date('Y-m-d', strtotime($previousEffectiveDate) - 3600 * 24);
        }
        $updateQuery = "UPDATE civicrm_hrjobcontract_revision SET " .
                       "effective_end_date = %1 " .
                       "WHERE id = %2";
        $updateParams = array(
          1 => array($effectiveEndDate, 'String'),
          2 => array($revisions->id, 'Integer'),
        );
        CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
        // If 'effective_date' is equal to previous effective date
        // we mark this revision as overrided by the one newly created.
        if ($previousEffectiveDate === $revisions->effective_date) {
          $updateOverridedQuery = "UPDATE civicrm_hrjobcontract_revision SET " .
                  "overrided = 1 " .
                  "WHERE id = %1";
          $updateOverridedParams = array(
            1 => array($revisions->id, 'Integer'),
          );
          CRM_Core_DAO::executeQuery($updateOverridedQuery, $updateOverridedParams);
        }
      }
      $previousEffectiveDate = $revisions->effective_date;
    }
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
