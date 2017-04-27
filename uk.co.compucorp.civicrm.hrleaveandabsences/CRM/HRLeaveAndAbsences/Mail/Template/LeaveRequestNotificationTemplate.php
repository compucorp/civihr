<?php

class CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotificationTemplate
  extends CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotificationTemplate {

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'CiviHR Leave Request Notification',
      'is_default' => 1,
      'sequential' => 1
    ]);

    return isset($result['values'][0]) ? $result['values'][0] : '';
  }
}
