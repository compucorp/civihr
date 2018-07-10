<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequestCalendarFeedConfig extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  private static $defaultParams = [
    'title' => 'Feed 1',
    'timezone' => 'America/Monterrey',
    'composed_of' => ['leave_type' => [1]],
    'visible_to' => []
  ];

  public static function fabricate($params = []) {
    $params = array_merge(static::$defaultParams, $params);

    if (empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    return LeaveRequestCalendarFeedConfig::create($params);
  }
}
