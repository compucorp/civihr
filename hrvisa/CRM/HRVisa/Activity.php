<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

class CRM_HRVisa_Activity {

  /* to associate scheduled 'Visa Expiration' activity
   * for contacts having 'Visa Required'
   */
  static function sync($contactId) {
    // get visa required value
    $getInfo = array(
      'entity_id' => $contactId,
      'return.Extended_Demographics:Is_Visa_Required' => 1,
    );
    $isVisaRequired = civicrm_api3('custom_value', 'get', $getInfo);
    $isVisaRequired = $isVisaRequired['count'] ? $isVisaRequired['values']["{$isVisaRequired['id']}"][0] : 0;

    // this api call will get visa expiration date
    // of immigration records for the contact
    $getInfo = array(
      'entity_id' => $contactId,
      'return.Immigration:End_Date' => 1,
    );
    $immigrationDateInfo = civicrm_api3('custom_value', 'get', $getInfo);
    $lastestVisaExpirationDate = NULL;
    if ($immigrationDateInfo['count'] > 0) {
      $lastestVisaExpirationDate = $immigrationDateInfo['values']["{$immigrationDateInfo['id']}"]['latest'];
    }

    // activity processing if immigration data found
    if ($immigrationDateInfo['count']) {
      // get 'Visa Expiration' activity for this contact
      $activityTypeId = self::getActivityTypeID('Visa Expiration');
      $activityStatuses = CRM_Core_OptionGroup::values('activity_status', FALSE, FALSE, FALSE, NULL, 'name');

      // to check if visa expiration activity exists for the input target_contact_id
      $activityGetParams = array(
        'contact_id' => $contactId,
        'activity_type_id' => $activityTypeId,
        'sequential' => 1,
      );
      $activities = civicrm_api3('activity', 'get', $activityGetParams);

      $activityId = NULL;
      $count = 0;
      foreach($activities['values'] as $val) {
        $activityId = $val['id'];
        $count++;
      }

      if ($count) {
        $activityParams = array();
        $activityParams['status_id'] =
          $isVisaRequired ? CRM_Utils_Array::key('Scheduled', $activityStatuses) : CRM_Utils_Array::key('Cancelled', $activityStatuses);
        $activityParams['activity_date_time'] = $lastestVisaExpirationDate;

        // check if count is one, if not log a error
        if ($count > 1) {
          // update the last activity and log a error
          $logError =
            "Multiple 'Visa Expiration' activities exists for target contact with id : {$contactId}, so updating last activity with id : {$activityId}";
          CRM_Core_Error::debug_log_message($logError);
        }
        $activityParams['id'] = $activityId;
        $result = civicrm_api3('activity', 'create', $activityParams);
      }
      else {
        // if no activity create a new one only if 'visa is required'
        if ($isVisaRequired) {
          $activityParams = array(
            'target_contact_id' => $contactId,
            'activity_type_id' => $activityTypeId,
            'subject' => 'Visa Expiration',
            'activity_date_time' => $lastestVisaExpirationDate,
            'status_id' => CRM_Utils_Array::key('Scheduled', $activityStatuses),
            'details' => 'Visa Expiration',
          );
          $result = civicrm_api3('activity', 'create', $activityParams);
        }
      }
    } // end of if for immgration info check
  }

  public static function getActivityTypeID($activityType) {
    return self::getActivityFieldOptionID('activity_type_id', $activityType);
  }

  public static function getActivityStatusID($activityStatus) {
    return self::getActivityFieldOptionID('status_id', $activityStatus);
  }

  private static function getActivityFieldOptionID($field, $option) {
    return CRM_Core_PseudoConstant::getKey(
      CRM_Activity_BAO_Activity::class,
      $field,
      $option
    );
  }
}
