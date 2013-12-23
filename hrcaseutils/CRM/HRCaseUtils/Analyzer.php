<?php
 class CRM_HRCaseUtils_Analyzer {
  /**
   * @var int
   */
  private $caseId;
 
  private $activities;
 
  /**
   * @var array
   */
  private $indices;
 
  public function __construct($caseId, $activityId ) {
    $this->caseId = $caseId;
    $this->activityID = $activityId;
    $this->flush();
  }

  /**
   * Determine if case includes an activity of given type/status
   *
   * @param string $type eg "Phone Call", "Interview Prospect", "Background Check"
   * @param string $status eg "Scheduled", "Completed"
   * @return bool
   */
  public function hasActivity($type, $status = NULL) {
    $idx = $this->getActivityIndex(array('activity_type_id', 'status_id'));
    $activityTypeGroup = civicrm_api3('option_group', 'get', array('name' => 'activity_type'));
    $activityType = array('name'=>$type,
                      'option_group_id' => $activityTypeGroup['id'],);
    $activityTypeID = civicrm_api3('option_value', 'get',$activityType );
    $activityTypeID = $activityTypeID['values'][$activityTypeID['id']]['value'];
    if($status) {
      $activityStatusGroup = civicrm_api3('option_group', 'get', array('name' => 'activity_status'));
      $activityStatus = array('name'=>$status,
                          'option_group_id' => $activityStatusGroup['id']);
      $activityStatusID = civicrm_api3('option_value', 'get', $activityStatus);
      $activityStatusID =  $activityStatusID['values'][$activityStatusID['id']]['value'];
    }
    if ($status === NULL) {
      return !empty($idx[$activityTypeID]);
    } else { 
      return !empty($idx[$activityTypeID][$activityStatusID]);
    }
  }
 
  /**
   * Get a list of all activities in the case
   *
   * @return array list of activity records (api/v3 format)
   */
  public function getActivities() {
    if ($this->activities === NULL) {
      $result1 =civicrm_api3('case', 'get', array('id' => $this->caseId ));
      $activityIDs = $result1['values'][$result1['id']]['activities'];
      $result = civicrm_api3('Activity', 'get', array('id' => $this->activityID));
      $this->activities = $result['values'];
    }
    return $this->activities;
  }
 
  /**
   * Get a list of all activities in the case (indexed by some property/properties)
   *
   * @param array $keys list of properties by which to index activities
   * @return array list of activity records (api/v3 format), indexed by $keys
   */
  public function getActivityIndex($keys) {
    $key = implode(";", $keys);
    if (! $this->indices) {
      $this->indices = CRM_Utils_Array::index(array('activity_type_id', 'status_id'), $this->getActivities());
    } 
    return $this->indices;
  }
 
  /**
   * Flush any cached information
   *
   * @return void
   */
  public function flush() {
    $this->case = NULL;
    $this->activities = NULL;
    $this->indices = array();
  }
} 