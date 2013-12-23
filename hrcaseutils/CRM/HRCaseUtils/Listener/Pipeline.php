<?php
class CRM_HRCaseUtils_Listener_Pipeline extends CRM_Case_Form_Activity {
  public function onChange(CRM_HRCaseUtils_Analyzer $analyzer, $objectRef) {
    if(isset($objectRef->activity_type_id)) { 
      // Get activity type name & status of the activity being completed
      $activityTypeGroup = civicrm_api3('option_group', 'get', array('name' => 'activity_type'));
      $activityType = array('value' => $objectRef->activity_type_id,
                        'option_group_id' => $activityTypeGroup['id'],
                      );
      $activityTypeID = civicrm_api3('option_value', 'get', $activityType );
      $activityTypeID = $activityTypeID['values'][$activityTypeID['id']]['name'];
      
      $activityStatusGroup = civicrm_api3('option_group', 'get', array('name' => 'activity_status'));
      $activityStatus = array('value' => $objectRef->status_id,
                          'option_group_id' => $activityStatusGroup['id']
                        );
      $activityStatusID = civicrm_api3('option_value', 'get', $activityStatus);
      $activityStatusID =  $activityStatusID['values'][$activityStatusID['id']]['name'];
      
      // Schedule Interview Prospect activity
      if($activityTypeID == 'Open Case' && $activityStatusID=='Completed') {
        if ($analyzer->hasActivity($activityTypeID, $activityStatusID)) {
          $nextActivity = 'Interview Prospect';
          // Get the activity ID for Interview Prospect
          $activityID =array('name'=> $nextActivity,
                             'option_group_id' => $activityTypeGroup['id'],);
          $activityTypeID = civicrm_api3('option_value', 'get', $activityID);
          $activityTypeID = $activityTypeID['values'][$activityTypeID['id']]['value'];
          $params = self::buildParams($activityTypeID, $nextActivity, $objectRef);
        }
      }
      // Schedule Background Check activity
      elseif($activityTypeID == 'Interview Prospect' && $activityStatusID=='Completed') {
        if ($analyzer->hasActivity($activityTypeID, $activityStatusID)) {
          $nextActivity = 'Background Check';
          // Get the activity ID for Background Check
          $activityID =array('name'=> $nextActivity,
                             'option_group_id' => $activityTypeGroup['id'],);
          $activityTypeID = civicrm_api3('option_value', 'get', $activityID);
          $activityTypeID = $activityTypeID['values'][$activityTypeID['id']]['value']; 
          $params = self::buildParams($activityTypeID, $nextActivity, $objectRef);
        }
      }

      // don't schedule other activities yet
      elseif($activityTypeID == 'Background Check' && $activityStatusID=='Completed') {
        return;
      }
      if(isset($params)) {
        $followupActivity = CRM_Activity_BAO_Activity::createFollowupActivity($objectRef->original_id, $params); 
        if ($followupActivity) {
          $caseParams = array(
                              'activity_id' => $followupActivity->id,
                              'case_id' => $objectRef->case_id ,
                              );
          CRM_Case_BAO_Case::processCaseActivity($caseParams);
          $analyzer->flush();
        }
      }
    }
  }
  
 function buildParams($activityTypeID, $nextActivity, $objectRef) {
      $activityDateTime = CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');
      $params = array('status_id' => 2,
                      'followup_activity_type_id' => $activityTypeID,
                      'followup_activity_subject' => $nextActivity,
                      'followup_date' => $activityDateTime[0],
                      'followup_date_time' => $activityDateTime[1],
                      'original_id' => $objectRef->original_id,
                      'case_id' => $objectRef->case_id,
                      );
      return $params;
    }
}
?>