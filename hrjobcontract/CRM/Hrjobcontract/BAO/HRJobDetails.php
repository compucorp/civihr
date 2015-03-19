<?php

class CRM_Hrjobcontract_BAO_HRJobDetails extends CRM_Hrjobcontract_DAO_HRJobDetails {
    
    static $_importableFields = array();
    
    /**
     * Create a new HRJobDetails based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_HRJob_DAO_HRJobDetails|NULL
     *
     */
    public static function create($params) {
        $hook = empty($params['id']) ? 'create' : 'edit';
        $previousDetailsRevisionId = null;
        
        if ($hook == 'create') {
            $previousRevisionResult = civicrm_api3('HRJobContractRevision', 'getcurrentrevision', array(
              'sequential' => 1,
              'jobcontract_id' => $params['jobcontract_id'],
            ));
            if (!empty($previousRevisionResult['values']['details_revision_id'])) {
                $previousDetailsRevisionId = $previousRevisionResult['values']['details_revision_id'];
            }
        }
        
        $instance = parent::create($params);
        
        // setting 'effective_date' if it's not set:
        $revision = civicrm_api3('HRJobContractRevision', 'get', array(
            'sequential' => 1,
            'jobcontract_id' => $params['jobcontract_id'],
            'id' => $instance->jobcontract_revision_id,
        ));
        if (!empty($revision['values'][0])) {
            $revisionData = array_shift($revision['values']);
            if (!$revisionData['effective_date']) {
                civicrm_api3('HRJobContractRevision', 'create', array(
                    'id' => $revisionData['id'],
                    'effective_date' => $instance->period_start_date,
                ));
            }
        }
        
        $revisionResult = civicrm_api3('HRJobContractRevision', 'get', array(
            'sequential' => 1,
            'id' => $instance->jobcontract_revision_id,
        ));
        $revision = CRM_Utils_Array::first($revisionResult['values']);
        
        $duplicate = CRM_Utils_Array::value('action', $params, $hook);
        if ($hook == 'create' && empty($revision['role_revision_id']) && $duplicate != 'duplicate' && empty($params['import'])) {
            //civicrm_api3('HRJobRole', 'create', array('jobcontract_id' => $revision['jobcontract_id'],'title' => $instance->title, 'location'=> $instance->location, 'percent_pay_role' => 100, 'jobcontract_revision_id' => $instance->jobcontract_revision_id));
            CRM_Hrjobcontract_BAO_HRJobRole::create(array('jobcontract_id' => $revision['jobcontract_id'],'title' => $instance->title, 'location'=> $instance->location, 'percent_pay_role' => 100, 'jobcontract_revision_id' => $instance->jobcontract_revision_id));
        }
        
        if ($previousDetailsRevisionId) {
            CRM_Core_BAO_File::copyEntityFile('civicrm_hrjobcontract_details', $previousDetailsRevisionId, 'civicrm_hrjobcontract_details', $revision['details_revision_id']);
        }
        
        return $instance;
    }
    
  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_Hrjobcontract_DAO_HRJobDetails();
    $dao->copyValues($params);
    return $dao->count();
  }

  /**
   * combine all the importable fields from the lower levels object
   *
   * The ordering is important, since currently we do not have a weight
   * scheme. Adding weight is super important
   *
   * @param int     $contactType     contact Type
   * @param boolean $status          status is used to manipulate first title
   * @param boolean $showAll         if true returns all fields (includes disabled fields)
   * @param boolean $isProfile       if its profile mode
   * @param boolean $checkPermission if false, do not include permissioning clause (for custom data)
   *
   * @return array array of importable Fields
   * @access public
   * @static
   */
  static function importableFields($contactType = 'HRJobDetails',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobDetails';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);
    $fields = null;

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobDetails::import();

      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobDetails::import());

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
