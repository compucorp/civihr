<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1031 {

  /**
   * Adds the Calendar Feeds menu.
   *
   * @return bool
   */
  public function upgrade_1031() {
    $result = civicrm_api3('Navigation', 'get', ['name' => 'leave_and_absence_calendar_feeds']);

    if ($result['count'] > 0) {
      return TRUE;
    }

    $leaveAndAbsenceNav = civicrm_api3('Navigation', 'get', ['name' => 'leave_and_absences']);

    if (empty($leaveAndAbsenceNav['id'])) {
      return TRUE;
    }

    $weight = CRM_Core_BAO_Navigation::calculateWeight($leaveAndAbsenceNav['id']);

    civicrm_api3('Navigation', 'create', [
      'label' => ts('Calendar Feeds'),
      'name' => 'leave_and_absence_calendar_feeds',
      'url' => 'civicrm/admin/leaveandabsences/calendar-feeds',
      'permission' => 'can administer calendar feeds',
      'parent_id' => $leaveAndAbsenceNav['id'],
      'weight' => $weight,
      'is_active' => 1
    ]);

    return TRUE;
  }
}
